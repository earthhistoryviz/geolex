#!/usr/bin/env python3
# -*- coding: utf-8 -*-

#!pip3 install pygmt
import sys
sys.path.insert(1, '/usr/lib/pygplates/revision28')
import pygplates
import csv
import re
import numpy as np
import pygmt
import itertools as it

# The geometries are the 'features to partition'

# Command-line params:
age = float(sys.argv[1])
outdirname = sys.argv[2]
age_label = str(age) + ' Ma'
fig = pygmt.Figure()
filename = outdirname + '/reconstructed_geom.gmt'

def lithoDictCreate():
    # dictionary with key as the lithology pattern names, values as the pattern color code for pygmt/GMT
    # Wen Du created this csv file that translated Prof Ogg's TSC pattern code to pattern color code for GMT

    # Note: ALL KEYS WILL BE LOWERCASE!!!!!
    try:
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
    except Exception as e:
        print(f'The error is {e}')

def pygplateReconstructions():
    print(outdirname)
    input_geometries = pygplates.FeatureCollection(outdirname + '/recon.geojson')

    # static polygons are the 'partitioning features'
    static_polygons = pygplates.FeatureCollection('./config/shapes_static_polygons_Merdith_et_al.gpml')

    coastlines = pygplates.FeatureCollection('./config/shapes_continents_Merdith_et_al.gpml')

    # The partition_into_plates function requires a rotation model, since sometimes this would be
    # necessary even at present day (for example to resolve topological polygons)
    rotation_model = pygplates.RotationModel('./config/1000_0_rotfile_Merdith_et_al.rot')

    # partition features
    partitioned_geometries = pygplates.partition_into_plates(static_polygons, rotation_model, input_geometries, partition_method=pygplates.PartitionMethod.most_overlapping_plate)

    pygplates.reconstruct(partitioned_geometries, rotation_model, outdirname + '/reconstructed_geom.gmt', age)

    # Reconstruct the geometries
    pygplates.reconstruct(static_polygons, rotation_model, outdirname + '/reconstructed_static_polygons.gmt', age)
    pygplates.reconstruct(coastlines, rotation_model, outdirname + '/reconstructed_shapes_continents_Merdith_et_al.gmt', age)

def extract(outdirname):
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

def labeling_shapes_with_names(patternList):
    sections = []
    with open(filename, 'r') as f:
        for key, group in it.groupby(f, lambda line: line.startswith('>')):
            if not key:
                sections.append(list(group))
    output = {}
    for i in range(1, len(sections)):
        info = sections[i][0].replace('\"', '').split('|')
        co_or = sections[i][2].replace('\n', '').split(' ')
        if len(info) == 5:
            output[info[3]] = co_or
        if(len(patternList) <= 3):
            fig.text(text=info[3], x=float(co_or[0]), y=float(co_or[1]), N=True, D="0.0/0.3c", font="7p,Helvetica-Bold,black")

def getPatternListFromGMTFile():
    patternList = []

    sections = []
    with open(filename, 'r') as f:
        for key, group in it.groupby(f, lambda line: line.startswith('>')):
            if not key:
                sections.append(list(group))
    output = {}
    for i in range(1, len(sections)):
        info = sections[i][0].replace('\"', '').split('|')
        co_or = sections[i][2].replace('\n', '').split(' ')
        if len(info) == 5:
            output[info[3]] = co_or # output should be matching the formation name with proper coordinates (BUT- in a lot of cases appears formation name isn't in info[3])
            patternList.append((info[4].replace('\n', '')).lower())
        else:
            patternList.append('unknown')
    return patternList

def plotting_shapes_and_lithology(patternList, patternDict):
    # The code to plot formations in different patterns according to their Lithology Pattern
    x_coordinates = [] # holds all of the x coordinates of the formation to be plotted
    y_coordinates = [] # holds all of the y coordinates of the formation to be plotted
    patternListIndex = 0 # index of the pattern Color in patternList of the formation to be plotted

    # reads all of the formation's coordinates except for the last one
    with open(outdirname + '/reconstructed_geom.gmt', 'r') as f:
        for data in f:
            data = data.rstrip()
            if bool(re.match(r'(^-?\d+.\d+ -?\d+.\d+)', data)): # only decimal numbers starting at the beginning of the line will be matched from the regex
                tempList = data.split(" ")
                # adds the x,y coordinates to the list
                x_coordinates.append(float(tempList[0]))
                y_coordinates.append(float(tempList[1]))
            if data == ">":
                if len(x_coordinates) != 0 and len(y_coordinates) != 0:
                    # converts the coordinate list to an Array so pygPlate can use a pattern
                    xArray = np.array(x_coordinates)
                    yArray = np.array(y_coordinates)

                    # get the pattern Key
                    try:
                        if len(patternList) == 0:
                            raise KeyError

                        patternColor = patternList[patternListIndex]
                        patternColor = patternDict[patternColor]

                    except KeyError:
                        patternColor = patternDict['unknown']

                    fig.plot(x=xArray, y=yArray, pen="0.25p,black", frame="afg30", color=patternColor)
                    x_coordinates.clear()
                    y_coordinates.clear()
                    patternListIndex += 1

    # plot the last formation (or the only formation)
    if len(x_coordinates) != 0 and len(y_coordinates) != 0:
        xArray = np.array(x_coordinates)
        yArray = np.array(y_coordinates)

        try:
            if len(patternList) == 0:
                raise KeyError

            patternColor = patternList[patternListIndex]
            patternColor = patternDict[patternColor]

        except KeyError:
            patternColor = patternDict['unknown']

        fig.plot(x=xArray, y=yArray, pen="0.25p,black", frame="afg30", color=patternColor)

        x_coordinates.clear()
        y_coordinates.clear()
        patternListIndex += 1
        labeling_shapes_with_names(patternList)

def plotting_inset(central_lon):
    with fig.inset(position="jTR+w4c", box="+pblack+gwhite"):
        # Use a plotting function to create a figure inside the inset
        # Make a global Robinson map (or any other projection that you like) filled with blue color and grid every 30 degree.
        fig.coast(region="g", projection="R" + str(central_lon) + "/?", frame="g30", land="skyblue", water="skyblue")

        # plot reconstructed polygons and coastlines onto the inset map
        fig.plot(data=outdirname+'/reconstructed_shapes_continents_Merdith_et_al.gmt', G="seashell", pen="0.1p,black")
        fig.plot(data=outdirname+'/reconstructed_geom.gmt', frame="g30", pen="1p,red")

        # place reconstruction age in the inset map: fig.text(text="TEST", position="TC", font="22p,Helvetica-Bold,black")
        # somehow, position="TC" means that it is in the Top central of the plotted earth (inset), we must drift 0/0.5c outside of the globe as this is an ideal position to place the text.
        fig.text(text=age_label, position="TC", D="0/0.5c", N=True, font="12p,Helvetica-Bold,black")

def main():
    patternDict = lithoDictCreate()
    pygplateReconstructions()
    edge_info = extract(outdirname)
    patternList = getPatternListFromGMTFile()

    # Finding the central view for creating the projection
    central_lon = (edge_info[0] + edge_info[1]) / 2
    central_lat = (edge_info[2] + edge_info[3]) / 2
    region_edge = str(edge_info[0] - 60) + "/" + str(edge_info[1] + 60) + "/" + str(edge_info[2] - 20) + "/" + str(edge_info[3] + 20)

    if abs(edge_info[2]) > 35 or abs(edge_info[3]) > 35: # if the boundary of map reaches beyond 55 degrees (35 after some conversions) north or south in latitude.
        if abs(edge_info[2] - edge_info[3]) < 80: # And, if all polygons are in the same hemisphere, plot polar projection.
            projection = "G" + str(central_lon) + "/" + str(central_lat) + "/60/7.5c"
            fig.coast(region="d", projection=projection, frame="a30g30", land="skyblue", water="skyblue")
            fig.plot(data=outdirname + '/reconstructed_shapes_continents_Merdith_et_al.gmt', G="seashell", pen="0.1p,black")
            plotting_shapes_and_lithology(patternList, patternDict)

        else: # if the boundary of map is beyongd 55 degrees north or south in latitude (near poles), but all polygons are NOT in the same hemisphere, plot global projection.
            projection = "W" + str(central_lon) + "/15c"
            fig.coast(region="d", projection=projection, frame="a30g30", land="skyblue", water="skyblue")
            fig.plot(data=outdirname + '/reconstructed_shapes_continents_Merdith_et_al.gmt', G="seashell", pen="0.1p,black")
            plotting_shapes_and_lithology(patternList, patternDict)

        fig.text(text=age_label, x=180, y=-90, N=True, D="5/0c", font="12p,Helvetica-Bold,black") # A reconstruction age stamp to the projection

    else: # when the boundary of map is within 55 degrees north and south in latitude, or say close to the equator, plot regional map projection (rectangle).
        fig.coast(region=region_edge, projection="M15c", frame="afg30", land="skyblue", water="skyblue")
        fig.plot(data=outdirname + '/reconstructed_shapes_continents_Merdith_et_al.gmt', G="seashell", pen="0.1p,black")
        plotting_shapes_and_lithology(patternList, patternDict)
        plotting_inset(central_lon)

    fig.savefig(outdirname + "/final_image.png", dpi="300") # TODO: Do you need some kind of unique filename to prevent conflicts if multiple users are online?

main()
