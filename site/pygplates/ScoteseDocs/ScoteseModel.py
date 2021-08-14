#!/usr/bin/env python3
# -*- coding: utf-8 -*-


# Assigning plate ids to features
import sys
sys.path.insert(1, '/usr/lib/pygplates/revision28')
import pygplates
# import pandas as pd
import csv

age = float(sys.argv[1])
grid = ""
#grid = "Map7a__Early_Miocene_020.eps"
outdirname = "../"+sys.argv[2]
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
  print(grid)
  
  # The geometries are the 'features to partition'
  print("input_geometries = "+outdirname+'/recon.geojson')
  input_geometries = pygplates.FeatureCollection(outdirname+'/recon.geojson')
  
  # static polygons are the 'partitioning features'
  static_polygons = pygplates.FeatureCollection('../config/Scotese/PALEOMAP_PlatePolygons.gpml')
  
  
  # The partition_into_plates function requires a rotation model, since sometimes this would be
  # necessary even at present day (for example to resolve topological polygons)
  rotation_model = pygplates.RotationModel('../config/Scotese/PALEOMAP_PlateModel.rot')
  
  # partition features
  partitioned_geometries = pygplates.partition_into_plates(static_polygons,
                                                         rotation_model,
                                                         input_geometries,
                                                         partition_method = pygplates.PartitionMethod.most_overlapping_plate)
  
  # Write the partitioned data set to a file
  #output_feature_collection = pygplates.FeatureCollection(partitioned_geometries)
  #output_feature_collection.write('Data/thai_partitioned.gpml')
  
  
  
  # Reconstruct features to age
  #age = 300.0
  
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

import pygmt
import itertools as it


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

fig = pygmt.Figure()

#Input prof. Christopher Scotese grid model, the 300Ma one or other jpeg


fig = pygmt.Figure()
with fig.subplot(nrows=1, ncols=2, figsize=("19c", "7c"), frame="lrtb", autolabel=True,margins=["0.0c", "0.0c"],title = str(age_label),FONT_HEADING=12 ):
    with fig.set_panel(panel=[0,0]):
        # plotting panel b
        print("1: Using grid = "+grid+", projection = "+projection_Panel_1)        
        #fig.grdimage (grid=grid, img_in = "Map1a_PALEOMAP_PaleoAtlas_000.jpg", region="d",projection=projection_Panel_1, panel=[0, 0])  
        fig.grdimage (grid=grid, region="d",projection=projection_Panel_1, panel=[0, 0])  
        fig.plot( data = outdirname+'/reconstructed_geom.gmt',pen="0.25p,black",color="255/240/161", panel=[0, 0]) 
        #fig.image(grid)
        fig.basemap(frame="g30")
        
        # plotting panel b
        print("2: Using grid = "+grid+", projection = "+projection_Panel_2)        
        #fig.grdimage (grid=grid,  img_in = "Map1a_PALEOMAP_PaleoAtlas_000.jpg",region="d",projection=projection_Panel_2, panel=[0, 1])
        fig.grdimage (grid=grid,  region="d",projection=projection_Panel_2, panel=[0, 1])
        fig.plot(data = outdirname+'/reconstructed_geom.gmt',pen="0.25p,black",color="255/240/161", panel=[0, 1])    
        #fig.image(grid)
        fig.basemap(frame="a30g30")
        # LABELING

        filename = outdirname+"/reconstructed_geom.gmt"

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
    


fig.savefig(outdirname+"/final_image.png",dpi="150") # Do you need some kind of unique filename to prevent conflicts if multiple users are online?

#fig.show()
