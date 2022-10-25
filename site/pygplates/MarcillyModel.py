#!/usr/bin/env python3
# -*- coding: utf-8 -*-
# Assigning plate ids to features
import sys
sys.path.insert(1, '/usr/lib/pygplates/revision28')
import pygplates
import os
import csv
import re
import numpy as np
import pygmt
import itertools as it


#Global Variables 
fig = pygmt.Figure() #plotting figures using pygmt
age = float(sys.argv[1]) #age of the formation being created
age_label = str(age) + ' Ma' #title of the graph

outdirname = sys.argv[2] #hashed folder name 
filename = outdirname+"/reconstructed_geom.gmt"


#Function lithoDictCreate: creates a dictionary with key as the Lithology Pattern Name and values as the PYGMT/GMT color/pattern code
#Name and patterns are read from the "TSCreator_litho-pattern_to_GMT-fixed_pattern_code.csv" file created by Wen Du and Professor Ogg's TSC pattern code to pattern color code for GMT --> NOTE: all keys will be in lowercase!
def lithoDictCreate():
    #Note: ALL KEYS WILL BE LOWERCASE!!!!! 
    patternDict = {}
    with open('./TSCreator_litho-pattern_to_GMT-fixed_pattern_code.csv') as csv_file:
        csv_reader = csv.reader(csv_file, delimiter=',')
        line_count = 0
        
        for row in csv_reader:
            key = row[0]
            if line_count >= 2:
                patternDict[key.lower()] = row[2]
            line_count += 1

    return patternDict


#Function getPatternListFromGMTFile: reads the "../reconstructed_geom.gmt" file to get all of the lithology patterns from the GMT file. It will return a list of all the lithology pattern names in the gmt file.
def getPatternListFromGMTFile():
    #adding all of the lithology pattern
    patternList = []

    try:
        sections = []
        with open(filename, 'r') as f:
            for key, group in it.groupby(f, lambda line: line.startswith('>')):
                if not key:
                    sections.append(list(group))
        for i in range(1, len(sections)):
            info = sections[i][0].replace('\"', '').split('|')
            patternList.append((info[4].replace('\n', '')).lower())
        
        return patternList

    except Exception as e:
        print(e)


#extract: Finds the edges of the "../reconstructed_geom.gmt" so that the image can adjust accordingly
def extract():
    with open(filename, 'r') as f:
        file = f.read()
        sections = file.split('>') # A list of the entire file separated by >
        lon = []
        lat = []
        for i in range(1, len(sections)): # one per formation
            info = sections[i].split('\n')
            for j in range(3, len(info) - 3): # one per pair of coordinates (starting from line 4)
                data = info[j]
                data = data.replace('\n','').split(' ')
                lon.append(float(data[0]))
                lat.append(float(data[1]))
    return [min(lon), max(lon), min(lat), max(lat)]




def plotting_coasts_and_land_subplots(patternList, patternDict):
    fig.plot(data = outdirname+'/reconstructed_CEED_land_simple.gmt',G="skyblue2",pen="0.1p,black")
    fig.plot(data = outdirname+'/reconstructed_CEED_Exposed_Land.gmt',G="bisque1@20",pen="0.1p,black")
    
    plotting_shapes_and_lithology(patternList, patternDict)
    # LABELING
    if(len(patternList) <= 3): #only will label the names if there are only <= 3 formations in the "../reconstructed_geom.gmt" file
        labeling_shapes_with_names()


#Function plotting_shapes_and_lithology pattern: reads the "../reconstructed_geom.gmt" file to get the GEOJSON to draw the shape of each reconstruction and fills the shape with the correct lithology pattern color

def plotting_shapes_and_lithology(patternList, patternDict):
    x_coordinates = []
    y_coordinates = []
    patternListIndex = 0
    with open(outdirname+'/reconstructed_geom.gmt', 'r') as f:
        for data in f:
            data = data.rstrip()
                                
            if(bool(re.match(r'(^-?\d+.\d+ -?\d+.\d+)', data))): #only decimal numbers starting at the beginning of the line will be matched from the regex
                tempList = data.split(" ") 
                #adds the x,y coordinates to the list
                x_coordinates.append(float(tempList[0])) 
                y_coordinates.append(float(tempList[1]))

            #if gmt file has only 1 polygon, the file will not have a ">" appended at the very end of the file, thus
            # lines 188 to 210 will not run, thus it will jump from line 213 to 230
            if(data == ">"):     
                if(len(x_coordinates) != 0 and len(y_coordinates) != 0):
                    #converts the coordinate list to an Array so pygPlate can use a pattern
                    xArray = np.array(x_coordinates) 
                    yArray = np.array(y_coordinates)

                    #get the pattern Key
                    try:
                        if(len(patternList) == 0):
                            raise KeyError
                        
                        patternColor = (patternList[patternListIndex])
                        patternColor = patternDict[patternColor] 

                    except KeyError:
                        patternColor = patternDict['unknown']
  
                    fig.plot(x=xArray, y=yArray, pen="0.25p,black",color=patternColor,frame="a30g30")
                    x_coordinates.clear()
                    y_coordinates.clear()
                    patternListIndex += 1

    #plot the last formation (or the only formation)
    if(len(x_coordinates) != 0 and len(y_coordinates) != 0):
        xArray = np.array(x_coordinates)
        yArray = np.array(y_coordinates)

        try:
            if(len(patternList) == 0):
                raise KeyError
            
            patternColor = (patternList[patternListIndex])
            patternColor = patternDict[patternColor] 

        except KeyError:
            patternColor = patternDict['unknown']
        
        fig.plot(x=xArray, y=yArray, pen="0.25p,black",color=patternColor, frame="a30g30")
        x_coordinates.clear()
        y_coordinates.clear()
        patternListIndex += 1   
     

   



#labeling_shapes_with_names: labels the shapes drawn by gmt with a formation name from "../reconstructed_geom.gmt" file.
#currently this function is only called if the there are only <= 3 formation names in the "../reconstructed_geom.gmt" or else it gets too cluttered to read...
def labeling_shapes_with_names():
    sections = []
    #reading file
    with open(filename, 'r') as f:
        for key, group in it.groupby(f, lambda line: line.startswith('>')):
            if not key:
                sections.append(list(group))

    output = {}
    for i in range(1, len(sections)):
        info = sections[i][0].replace('\"', '').split('|')
        co_or = sections[i][2].replace('\n', '').split(' ')
        output[info[3]] = co_or

        # labeling 
        fig.text(text=info[3], x=float(co_or[0]), y=float(co_or[1]), N=True, D="0/0.2c", font="7p,Helvetica-Bold,black")
                
             

#creating_subplots
def creating_figures(edge_info, patternList, patternDict):
    if edge_info[2] >= -40 and edge_info[3] <= 40:
        with fig.inset(position="jTR+w4c", box="+pblack+gwhite"):
        # Use a plotting function to create a figure inside the inset
        # Make a global Robinson map (or any other projection that you like) filled with blue color and grid every 30 degree.
            fig.coast (region="g", projection="R?",land="skyblue",water="skyblue")

            #plot reconstructed polygons and coastlines onto the inset map 
            fig.plot(data = outdirname+'/reconstructed_CEED_land_simple.gmt',G="skyblue2",pen="0.1p,black")
            fig.plot(data = outdirname+'/reconstructed_CEED_Exposed_Land.gmt',G="bisque1@20",pen="0.1p,black")
            fig.plot(data = outdirname+'/reconstructed_geom.gmt',color="red", frame="g30")     
            


            # place reconstruction age in the inset map : fig.text(text="TEST", x=lon, y=lat, font="22p,Helvetica-Bold,black")
            #somehow, X=180, Y=45 is an ideal position to place the text. 
            fig.text(text=age_label, x=180, y=45, N=True, D="0/1c", font="12p,Helvetica-Bold,black")  
        
    else:
        fig.text(text=age_label, x=180, y=-90, N=True, D="5/0c", font="12p,Helvetica-Bold,black") 
           
    fig.savefig(outdirname+"/final_image.png",dpi="300")


def pygplateReconstructions():
    # The geometries are the 'features to partition'
        input_geometries = pygplates.FeatureCollection(outdirname+'/recon.geojson')
         


        # land_simple is the 'partitioning features', Land polygons of today
        static_polygons = pygplates.FeatureCollection('./config/Land_Simple_CEED_2021.gpml')
        
        # Exposed land in 10 Myrs interval
        exposed_Land = pygplates.FeatureCollection('./config/Exposed_Land_CEED_2021.gpml')
        
        #Some terrane polygons]
        terranes_simple = pygplates.FeatureCollection('./config/Terranes_Simple_CEED2021.gpml')
        
        # The partition_into_plates function requires a rotation model, since sometimes this would be
        # necessary even at present day (for example to resolve topological polygons)
        # Torsvik's Rotation file: 520-0 Ma
        rotation_model = pygplates.RotationModel('./config/CEED_ROTATION_ENGINE_CHLOE.rot')


        # partition features
        partitioned_geometries = pygplates.partition_into_plates(static_polygons, rotation_model, input_geometries, partition_method = pygplates.PartitionMethod.most_overlapping_plate)
        
        # Write the partitioned data set to a file

        pygplates.reconstruct(partitioned_geometries, rotation_model, outdirname+ '/reconstructed_geom.gmt', age, anchor_plate_id=1)  
        

        pygplates.reconstruct(static_polygons, rotation_model, outdirname+ '/reconstructed_CEED_land_simple.gmt', age, anchor_plate_id=1)
        
        
        pygplates.reconstruct(exposed_Land, rotation_model, outdirname+ '/reconstructed_CEED_Exposed_Land.gmt', age, anchor_plate_id=1) 
       


def main():

    try:
        patternDict = lithoDictCreate()

        #reconstructions using pygplates
        pygplateReconstructions()

        #finding the edges of the polygons
        edge_info = extract()
        #Finding the central view for creating the projection
        central_lon= (edge_info[0]+edge_info[1])/2
        central_lat= (edge_info[2]+edge_info[3])/2
        region_edge = str(edge_info[0]-60) + "/" + str(edge_info[1]+60) + "/" + str(edge_info[2]-20) + "/" + str(edge_info[3]+20)

    
        if edge_info[2] < -40 or edge_info[3] > 40:
            central= (edge_info[0]+edge_info[1])/2
            projection = "W" + str(central) + "/15c"
            fig.coast (region="d",projection=projection, frame="a30g30",land="skyblue",water="skyblue") #
        else: #use whole earth because it's closer to the poles
            fig.coast (region=region_edge, projection="M15c", frame="afg30",land="skyblue",water="skyblue")


        patternList = getPatternListFromGMTFile() 
        plotting_coasts_and_land_subplots(patternList, patternDict)
        
        creating_figures(edge_info, patternList, patternDict)


    except Exception as e:
        print(e)

main()
