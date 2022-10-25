#!/usr/bin/env python3
# -*- coding: utf-8 -*-
# Assigning plate ids to features
import sys
sys.path.insert(1, '/usr/lib/pygplates/revision28')
import pygplates
import csv
import pygmt
import itertools as it
import re
import numpy as np
import os


age = float(sys.argv[1])
age_label = str(age) + ' Ma'
grid = ""
outdirname = sys.argv[2]
filename = outdirname + "/reconstructed_geom.gmt"
#path of the current directory
pathCurrent = os.getcwd()
#path of parent directory
pathParent = os.path.dirname(pathCurrent)

scoteseDocsPath = pathParent + "/ScoteseDocs"

fig = pygmt.Figure()

#pygplates/site/dev/resources
pygmt.makecpt(cmap= scoteseDocsPath + "/Scotese.cpt")



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
    #adding all of the lithology pattern

    patternList = []
    sections = []
    with open(filename, 'r') as f:
        for key, group in it.groupby(f, lambda line: line.startswith('>')):
            if not key:
                sections.append(list(group))
    for i in range(1, len(sections)):
        info = sections[i][0].replace('\"', '').split('|')
        patternList.append((info[4].replace('\n', '')).lower())
        
    return patternList

def getGrid():
    if age == 0:
        grid = "Map01_PALEOMAP_6min_Holocene_0Ma.nc"
    else:
        with open( scoteseDocsPath + "/Scotese_DEM_high_resolution/Age_duration_of_Scotese_DEM_high_resolution_GST2020_V3.csv", 'r') as file:
            
            lookup = csv.reader(file)
            next(lookup, None)
            for row in lookup:
                if row[0] == "":
                    break
                if age <= float(row[1]) and age > float(row[2]):
                    grid = row[0]
                    break
    
    if grid == "":
        print("No reconstrution available for {}".format(age))
        exit(1)
    
    return pathParent + '/ScoteseDocs/Scotese_DEM_high_resolution/' + grid

def pygplateReconstructions():
    # The geometries are the 'features to partition'
    input_geometries = pygplates.FeatureCollection(outdirname+'/recon.geojson')

    # static polygons are the 'partitioning features'
    static_polygons = pygplates.FeatureCollection('./config/Scotese/PALEOMAP_PlatePolygons.gpml')


    # The partition_into_plates function requires a rotation model, since sometimes this would be
    # necessary even at present day (for example to resolve topological polygons)
    rotation_model = pygplates.RotationModel('./config/Scotese/PALEOMAP_PlateModel.rot')

    # partition features
    partitioned_geometries = pygplates.partition_into_plates(static_polygons, rotation_model, input_geometries, partition_method = pygplates.PartitionMethod.most_overlapping_plate)


    # Reconstruct the geometries


    pygplates.reconstruct(static_polygons,
                        rotation_model,
                        outdirname+'/reconstructed_static_polygons.gmt',
                        age)  


    pygplates.reconstruct(partitioned_geometries,
                        rotation_model,
                        outdirname+'/reconstructed_geom.gmt',
                        age)

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

#Function plotting_shapes_and_lithology pattern: reads the "../reconstructed_geom.gmt" file to get the GEOJSON to draw the shape of each reconstruction and fills the shape with the correct lithology pattern color
def plotting_shapes_and_lithology(patternList, patternDict, frametext):
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

                
                    
                    fig.plot(x=xArray, y=yArray, pen="0.25p,black",color=patternColor, frame=frametext)
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
        
        fig.plot(x=xArray, y=yArray, pen="0.25p,black",color=patternColor, frame = frametext)
        x_coordinates.clear()
        y_coordinates.clear()
        patternListIndex += 1  



#labeling_shapes_with_names: labels the shapes drawn by gmt with a formation name from "../reconstructed_geom.gmt" file.
#currently this function is only called if the there are only <= 3 formation names in the "../reconstructed_geom.gmt" or else it gets too cluttered to read...
def labeling_shapes_with_names(patternList):
    # LABELING
    sections = []
    with open(filename, 'r') as f:
        for key, group in it.groupby(f, lambda line: line.startswith('>')):
            if not key:
                sections.append(list(group))
        output = {}
        for i in range(1, len(sections)):
            info = sections[i][0].replace('\"', '').split('|')
            co_or = sections[i][2].replace('\n', '').split(' ')
            output[info[3]] = co_or

            if(len(patternList) <= 3):
                fig.text(text=info[3], x=float(co_or[0]), y=float(co_or[1]), N=True, D="0/0.2c", font="4.5p,Helvetica-Bold,black")

def main():
    try:
        patternDict = lithoDictCreate()
        grid = getGrid()
        pygplateReconstructions()
        patternList = getPatternListFromGMTFile()

        
        #Finding the central view for creating the projection
        edge_info = extract()
        central_lon= (edge_info[0]+edge_info[1])/2
        central_lat= (edge_info[2]+edge_info[3])/2
        region_edge = str(edge_info[0]-60) + "/" + str(edge_info[1]+60) + "/" + str(edge_info[2]-20) + "/" + str(edge_info[3]+20)
        
        if edge_info[2] < -55 or edge_info[3] > 55: # if the boundary of map reaches beyongd 55 degress north or south in latitude.
            if edge_info[2] - edge_info[3] < 80: # And, if all polygons are in the same  hemisphere, plot polar projection. 
                fig.grdimage (grid=grid, region="d",projection="G"+ str(central_lon)+"/"+str(central_lat)+ "/60/15c",shading="+d")  # plot base grid map (Scotese' DEM).the eye view is centered based on the center of all polygons. "60" is a horizon parameter.

                plotting_shapes_and_lithology(patternList, patternDict, "g30")

            else: #if the boundary of map is beyongd 55 degress north or south in latitude (near poles), but all polygons are NOT in the same  hemisphere, plot global projection.
                central= (edge_info[0]+edge_info[1])/2
                projection = "W" + str(central) + "/15c"
                fig.grdimage (grid=grid,region="d",projection=projection,shading="+d") 

                plotting_shapes_and_lithology(patternList, patternDict, "a30g30")

                fig.text(text=age_label, x=180, y=-90, N=True, D="5/0c", font="12p,Helvetica-Bold,black")# A reconstruction age stamp to the global projection
        
        else: # when the boundary of map is within 55 degress north and south in latitude, or say close to the equator, plot regional map projection.
            
            fig.grdimage  (grid=grid,region=region_edge, projection="M15c",shading="+d") #plot base grid map (Scotese' DEM).
            plotting_shapes_and_lithology(patternList, patternDict, frametext="a30g30")
            with fig.inset(position="jTR+w4c", box="+pblack+gwhite"):# create a corner figure inside the inset
                fig.grdimage (grid=grid,region="g", projection="R"+ str(central_lon) +"/?")
                #plot reconstructed polygons and coastlines onto the inset map
                fig.plot(data = filename ,color="red", frame="g30")

                # place reconstruction age in the inset map : fig.text(text="TEST", x=lon, y=lat, font="22p,Helvetica-Bold,black")
                #somehow, X=180, Y=45 is an ideal position to place the text.
                fig.text(text=age_label, x=180, y=90, N=True, D="-0.25/0.25c", font="10p,Helvetica-Bold,black")  


        labeling_shapes_with_names(patternList)
        #fig.colorbar(frame=["xa2000f500+lElevation", "y+lm"]) # set the bottom Color-Elevation bar to either plot.
        fig.savefig(outdirname+"/final_image.png",dpi="300") 

    except Exception as e:
        print(e)

main()
