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
grid = ""
outdirname = "../"+sys.argv[2]


#path of the current directory
pathCurrent = os.getcwd()
#path of parent directory
pathParent = os.path.dirname(pathCurrent)


#dictionary with key as the lithology pattern names, values as the pattern color code for pygmt/GMT
#Wen Du created this csv file that translated Prof Ogg's TSC pattern code to pattern color code for GMT

#Note: ALL KEYS WILL BE LOWERCASE!!!!! 
patternDict = {}
with open(pathParent+'/TSCreator_litho-pattern_to_GMT-fixed_pattern_code.csv') as csv_file:
    csv_reader = csv.reader(csv_file, delimiter=',')
    line_count = 0
    
    for row in csv_reader:
        key = row[0]
        if line_count >= 2:
            patternDict[key.lower()] = row[2]
        line_count += 1


try: 
  if age == 0:
      grid = "Map1a_PALEOMAP_PaleoAtlas_000.jpg"
  else:
      with open('ScoteseLookUp1.csv', 'r') as file:
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
  grid = './backgrounds/' + grid
  #print(grid)
  
  # The geometries are the 'features to partition'
  #print("input_geometries = "+outdirname+'/recon.geojson')

  input_geometries = pygplates.FeatureCollection(outdirname+'/recon.geojson')
  
  # static polygons are the 'partitioning features'
  static_polygons = pygplates.FeatureCollection('../config/Scotese/PALEOMAP_PlatePolygons.gpml')
  
  
  # The partition_into_plates function requires a rotation model, since sometimes this would be
  # necessary even at present day (for example to resolve topological polygons)
  rotation_model = pygplates.RotationModel('../config/Scotese/PALEOMAP_PlateModel.rot')
  
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
except Exception as e:
  print(e)

# Plot using pyGMT


def extract(outdirname):
    filename = outdirname+'/reconstructed_geom.gmt'
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

#Finding the central view for creating the projection
edge_info = extract(outdirname)
central_lon= (edge_info[0]+edge_info[1])/2
central_lat= (edge_info[2]+edge_info[3])/2
projection_Panel_1 = "G"+ str(central_lon)+"/"+str(central_lat)+ "60/?"
projection_Panel_2 = "W" + str(central_lon) + "/?"


age_label = str(age) + ' Ma'


#adding all of the lithology pattern
filename = outdirname+"/reconstructed_geom.gmt"
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
       
except Exception as e:
    print(e)

#Input prof. Christopher Scotese grid model, the 300Ma one or other jpeg

fig = pygmt.Figure()
with fig.subplot(nrows=1, ncols=2, figsize=("19c", "7c"), frame="lrtb", autolabel=True, margins=["0.0c", "0.0c"],title = str(age_label),FONT_HEADING=12 ):
    with fig.set_panel(panel=[0,0]):
        for index in range(0,2):
            if(index == 1):
                fig.grdimage (grid=grid, region="d",projection=projection_Panel_1, panel=[0, index])   
                frametext = "g30"
            else:
                fig.grdimage (grid=grid,  region="d",projection=projection_Panel_2, panel=[0, index])
                frametext = "a30g30"

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
                
                fig.plot(x=xArray, y=yArray, pen="0.25p,black",color=patternColor, panel=[0, index])
                x_coordinates.clear()
                y_coordinates.clear()
                patternListIndex += 1  



            fig.basemap(frame=frametext)


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
                    # labeling panel a
                    fig.text(text=info[3], x=float(co_or[0]), y=float(co_or[1]), N=True, D="0/0.2c", font="4.5p,Helvetica-Bold,black", panel=[0, 0])
                    # labeling panel b
                    fig.text(text=info[3], x=float(co_or[0]), y=float(co_or[1]), N=True, D="0/0.2c", font="4.5p,Helvetica-Bold,black", panel=[0, 1])

fig.savefig(outdirname+"/final_image.png",dpi="150") # Do you need some kind of unique filename to prevent conflicts if multiple users are online?

#fig.show()
