#!/usr/bin/env python3
# -*- coding: utf-8 -*-

#!pip3 install pygmt
import sys
sys.path.insert(1, '/usr/lib/pygplates/revision28')
import pygplates
import csv
import re
import numpy as np
# The geometries are the 'features to partition'

# Command-line params:
age = float(sys.argv[1])
outdirname = sys.argv[2]

#dictionary with key as the lithology pattern names, values as the pattern color code for pygmt/GMT
#Wen Du created this csv file that translated Prof Ogg's TSC pattern code to pattern color code for GMT

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


try:
    input_geometries = pygplates.FeatureCollection(outdirname+'/recon.geojson')
    # static polygons are the 'partitioning features'
    static_polygons = pygplates.FeatureCollection('./config/shapes_static_polygons_Merdith_et_al.gpml')

    coastlines = pygplates.FeatureCollection('./config/Global_EarthByte_GPlates_PresentDay_ContinentalPolygons_2019_v1.shp')
    # The partition_into_plates function requires a rotation model, since sometimes this would be
    # necessary even at present day (for example to resolve topological polygons)
    rotation_model = pygplates.RotationModel('./config/1000_0_rotfile_Merdith_et_al.rot')

    # partition features
    partitioned_geometries = pygplates.partition_into_plates(static_polygons, rotation_model, input_geometries, partition_method = pygplates.PartitionMethod.most_overlapping_plate)


    # Reconstruct the geometries
    pygplates.reconstruct(static_polygons, rotation_model, outdirname+'/reconstructed_static_polygons.gmt', age) 
    pygplates.reconstruct(coastlines, rotation_model, outdirname+'/reconstructed_Global_EarthByte_GPlates_PresentDay_ContinentalPolygons_2019_v1.gmt', age) 

    pygplates.reconstruct(partitioned_geometries, rotation_model,outdirname+'/reconstructed_geom.gmt', age)  
    

except Exception as e:
    print(e)


try:
    import pygmt
except Exception as Ex:
    print(Ex)

import itertools as it

def extract(outdirname):
    try:
        filename = outdirname+'/reconstructed_geom.gmt'
    except Exception as Ex:
        print(Ex)
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


age_label = str(age) + ' Ma'

fig = pygmt.Figure()

# Make a regional Mercator map with grid interval=30 degree; land and water= skyblue create a blue basemap. 
# Otherwise, if use the basemap format, the base-color cannot be filled with blue.
#print("Before function")
edge_info = extract(outdirname)
region_edge = str(edge_info[0]-60) + "/" + str(edge_info[1]+60) + "/" + str(edge_info[2]-20) + "/" + str(edge_info[3]+20)

if edge_info[2] < -40 or edge_info[3] > 40:

    central= (edge_info[0]+edge_info[1])/2
    projection = "W" + str(central) + "/15c"
    fig.coast (region="d",projection=projection, frame="a30g30",land="skyblue",water="skyblue") #
    
else:
    fig.coast (region=region_edge, projection="M15c", frame="afg30",land="skyblue",water="skyblue") #



fig.plot(data = outdirname+'/reconstructed_Global_EarthByte_GPlates_PresentDay_ContinentalPolygons_2019_v1.gmt',G="seashell",pen="0.1p,black")

# LABELING
filename = outdirname+"/reconstructed_geom.gmt"
patternList = []

try:
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
        patternList.append((info[4].replace('\n', '')).lower())
        fig.text(text=info[3], x=float(co_or[0]), y=float(co_or[1]), N=True, D="+j0.0/0.3c", font="7p,Helvetica-Bold,black")
except Exception as e:
    print(e)


#The code to plot formations in different patterns according to their Lithology Pattern
x_coordinates = [] #holds all of the x coordinates of the formation to be plotted
y_coordinates = [] #holds all of the y coordinates of the formation to be plotted
patternListIndex = 0 #index of the pattern Color in patternList of the formation to be plotted

#reads all of the formation's coordinates except for the last one 
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

               
                
                fig.plot(x=xArray, y=yArray, pen="0.25p,black",color=patternColor)
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


# WITH FRONTS - fig.plot(data = 'reconstructed_geom.gmt',pen="1p,red",style="f1c/0.25c")

if edge_info[2] >= -40 and edge_info[3] <= 40:
    with fig.inset(position="jTR+w4c", box="+pblack+gwhite"):
    # Use a plotting function to create a figure inside the inset
    # Make a global Robinson map (or any other projection that you like) filled with blue color and grid every 30 degree.
        fig.coast (region="g", projection="R?", frame="g30",land="skyblue",water="skyblue")
        #plot reconstructed polygons and coastlines onto the inset map
        
        fig.plot(data = outdirname+'/reconstructed_Global_EarthByte_GPlates_PresentDay_ContinentalPolygons_2019_v1.gmt',G="seashell",pen="0.1p,black")
        # place reconstruction age in the inset map : fig.text(text="TEST", x=lon, y=lat, font="22p,Helvetica-Bold,black")
        #somehow, X=180, Y=45 is an ideal position to place the text.
        fig.text(text=age_label, x=180, y=45, N=True, D="0/1c", font="12p,Helvetica-Bold,black")  

# WITH FRONTS - fig.plot(data = 'reconstructed_geom.gmt',pen="1p,red",style="f1c/0.25c")
else:
    fig.text(text=age_label, x=180, y=-90, N=True, D="5/0c", font="12p,Helvetica-Bold,black")   

fig.savefig(outdirname+"/final_image.png") # Do you need some kind of unique filename to prevent conflicts if multiple users are online?

