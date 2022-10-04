#!/usr/bin/env python3
# -*- coding: utf-8 -*-
# Assigning plate ids to features
import sys
sys.path.insert(1, '/usr/lib/pygplates/revision28')
import pygplates
import os
import itertools as it
import csv
import re
import numpy as np
import pygmt
from importlib import reload
import timeit
import asyncio
import multiprocessing as mp

#Global Variables 
fig = pygmt.Figure() #plotting figures using pygmt
age = float(sys.argv[1]) #age of the formation being created
age_label = str(age) + ' Ma' #title of the graph

outdirname = sys.argv[2] #hashed folder name 
filename = outdirname+"/reconstructed_geom.gmt"


start =  timeit.default_timer()


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


#function plotting_coasts_and_land_subplots: using pygmt to plot the coasts and land of the graph. index 1 will plot the panel b
#index 0 will plot panel a. The Marcilly plot is created with 2 panels combined. The left panel is a, the right panel is b.

#async def plotting_coasts_and_land_subplots(projection_Panel_1, projection_Panel_2, index, patternList, patternDict):
def plotting_coasts_and_land_subplots(projection_Panel_1, projection_Panel_2, index, patternList, patternDict):
    
    #await asyncio.sleep(1)
    
    if(index == 1):
        #region = "d" sets the region to be th entire globe
        # 180W to 180E (-180, 180) and 90S to 90N (-90 to 90). With no parameters set for the projection, the figure defaults to be centered at the mid-point of both x- and y-axes. Using d, the figure is centered at (0, 0), or the intersection of the equator and prime meridian.
        fig.coast (region="d",projection=projection_Panel_1,land="skyblue",water="skyblue")
        frametext = "g30"
        stop = timeit.default_timer()
        print("plot coast done: "+ "index: "+ str(index) + " time: "  + str(stop - start))

    else:
        
        fig.coast (region="d",projection=projection_Panel_2, land="skyblue",water="skyblue")
        frametext ="a30g30"
        stop = timeit.default_timer()
        print("plot coast done: "+ "index: "+ str(index) + " time: "  + str(stop - start))


    fig.plot(data = outdirname+'/reconstructed_CEED_land_simple.gmt',G="skyblue2",pen="0.1p,black")
    stop = timeit.default_timer()
    print("land_simple_ line 166: "  + str(stop - start))
    fig.plot(data = outdirname+'/reconstructed_CEED_Exposed_Land.gmt',G="bisque1@20",pen="0.1p,black")
    stop = timeit.default_timer()
    print("exposed_land line 169: "  + str(stop - start))
    
    plotting_shapes_and_lithology(patternList, patternDict, frametext)

    #return frametext

#Function plotting_shapes_and_lithology pattern: reads the "../reconstructed_geom.gmt" file to get the GEOJSON to draw the shape of each reconstruction and fills the shape with the correct lithology pattern color

def plotting_shapes_and_lithology(patternList, patternDict, frametext):
    #def plotting_shapes_and_lithology(patternList, patternDict, frametext):
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
  
                    fig.plot(x=xArray, y=yArray, pen="0.25p,black",color=patternColor, panel=[0, index])
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
        
        fig.plot(x=xArray, y=yArray, pen="0.25p,black",color=patternColor)
        x_coordinates.clear()
        y_coordinates.clear()
        patternListIndex += 1    

    fig.basemap(frame=frametext)



#labeling_shapes_with_names: labels the shapes drawn by gmt with a formation name from "../reconstructed_geom.gmt" file.
#currently this function is only called if the there are only <= 3 formation names in the "../reconstructed_geom.gmt" or else it gets too cluttered to read...
def labeling_shapes_with_names():
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
            # labeling panel a
            
            fig.text(text=info[3], x=float(co_or[0]), y=float(co_or[1]), N=True, D="0/0.2c", font="4.5p,Helvetica-Bold,black", panel=[0, 0])
                
            # labeling panel b
            fig.text(text=info[3], x=float(co_or[0]), y=float(co_or[1]), N=True, D="0/0.2c", font="4.5p,Helvetica-Bold,black", panel=[0, 1])       

#creating_subplots
#async def creating_subplots(projection_Panel_1, projection_Panel_2, patternList, patternDict):

def creating_subplots(projection_Panel_1, projection_Panel_2, patternList, patternDict, index):
    
    with fig.set_panel(panel=index):
        plotting_coasts_and_land_subplots(projection_Panel_1, projection_Panel_2, index,patternList, patternDict)                
            

    # async def one_iteration(index):
    #     await plotting_coasts_and_land_subplots(projection_Panel_1, projection_Panel_2, index, patternList, patternDict) 
        
        
    #     stop = timeit.default_timer()
    #     print("INDEX CREATING PLOT: " + str(index) + "                  TIME:" + str(stop - start)) 

    
    # looping = [one_iteration(index) for index in range(2)]
    # await asyncio.gather(*looping)

           
    


def pygplateReconstructions():
    # The geometries are the 'features to partition'
        input_geometries = pygplates.FeatureCollection(outdirname+'/recon.geojson')
        stop = timeit.default_timer()
        print("input_geometries done: " + str(stop - start))  




        # land_simple is the 'partitioning features', Land polygons of today
        static_polygons = pygplates.FeatureCollection('./config/Land_Simple_CEED_2021.gpml')
        stop = timeit.default_timer()
        print("static_polygon done: " + str(stop - start))
        # Exposed land in 10 Myrs interval
        exposed_Land = pygplates.FeatureCollection('./config/Exposed_Land_CEED_2021.gpml')
        stop = timeit.default_timer()
        print("exposed_land: " + str(stop - start))
        #Some terrane polygons]
        terranes_simple = pygplates.FeatureCollection('./config/Terranes_Simple_CEED2021.gpml')
        stop = timeit.default_timer()
        print("terranes_simple: " + str(stop - start))
        # The partition_into_plates function requires a rotation model, since sometimes this would be
        # necessary even at present day (for example to resolve topological polygons)
        # Torsvik's Rotation file: 520-0 Ma
        rotation_model = pygplates.RotationModel('./config/CEED_ROTATION_ENGINE_CHLOE.rot')
        stop = timeit.default_timer()
        print("rotation_model: " + str(stop - start))





        # partition features
        partitioned_geometries = pygplates.partition_into_plates(static_polygons, rotation_model, input_geometries, partition_method = pygplates.PartitionMethod.most_overlapping_plate)
        stop = timeit.default_timer()
        print("partitioned_geometries: " + str(stop - start))
        # Reconstruct features to age
        # Reconstruct the geometries

        pygplates.reconstruct(partitioned_geometries, rotation_model, outdirname+ '/reconstructed_geom.gmt', age, anchor_plate_id=1)  
        stop = timeit.default_timer()
        print("reconstruction: " + str(stop - start))

        pygplates.reconstruct(static_polygons, rotation_model, outdirname+ '/reconstructed_CEED_land_simple.gmt', age, anchor_plate_id=1)
        stop = timeit.default_timer()
        print("reconstruction_land: " + str(stop - start)) 
        
        pygplates.reconstruct(exposed_Land, rotation_model, outdirname+ '/reconstructed_CEED_Exposed_Land.gmt', age, anchor_plate_id=1) 
        stop = timeit.default_timer()
        print("reconstruction exposed land: " + str(stop - start))


def main():
    patternDict = lithoDictCreate()

    try:
        pygplateReconstructions()
        

        #Finding the central view for creating the projection
        edge_info = extract()
        central_lon= (edge_info[0]+edge_info[1])/2
        central_lat= (edge_info[2]+edge_info[3])/2

    
        #"glon0/lat0[/horizon]/scale or Glon0/lat0[/horizon]/width"
        projection_Panel_1 = "G"+ str(central_lon)+"/"+str(central_lat)+ "60/?"
        #G = Orthographic projection
        # lon0/lat0 specifies the projection center, the optional parameter horizon specifies the maximum distance from projection center (in degrees, <= 90, default 90), and scale and width set the figure size.

        

        #w[lon0/]scale or W[lon0/]width
        projection_Panel_2 = "W" + str(central_lon) + "/?"
        #W = Mollweide projection
        #The central meridian is set with the optional lon0, and the figure size is set with scale or width.


        patternList = getPatternListFromGMTFile() 
        # loop = asyncio.get_event_loop()
        # loop.run_until_complete(creating_subplots(projection_Panel_1, projection_Panel_2, patternList, patternDict))
        with fig.subplot(nrows=1, ncols=2, frame="lrtb", autolabel=True, figsize = ("19c", "7c"), margins=["0.0c", "0.0c"], title = str(age_label),FONT_HEADING=12 ):
            for index in range(0,2):
                creating_subplots(projection_Panel_1, projection_Panel_2, patternList, patternDict, index)
            # pool = mp.Pool(mp.cpu_count())
            # result = [pool.apply(creating_subplots, args = (projection_Panel_1, projection_Panel_2, patternList, patternDict, index) ) for index in range(2)]

            
            # LABELING
            if(len(patternList) <= 3): #only will label the names if there are only <= 3 formations in the "../reconstructed_geom.gmt" file
                labeling_shapes_with_names()
            
        fig.savefig(outdirname+"/final_image.png",dpi="150")


    except Exception as e:
        print(e)

main()