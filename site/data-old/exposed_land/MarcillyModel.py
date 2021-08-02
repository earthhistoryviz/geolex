#!/usr/bin/env python3
# -*- coding: utf-8 -*-
# Assigning plate ids to features
import pygplates
import pandas as pd

# The geometries are the 'features to partition'
input_geometries = pygplates.FeatureCollection('recon.geojson')

# land_simple is the 'partitioning features', Land polygons of today
static_polygons = pygplates.FeatureCollection('Land_Simple_CEED_2021.gpml')
# Exposed land in 10 Myrs interval
exposed_Land = pygplates.FeatureCollection('Exposed_Land_CEED_2021.gpml')
#Some terrane polygons]
terranes_simple = pygplates.FeatureCollection('Terranes_Simple_CEED2021.gpml')

# The partition_into_plates function requires a rotation model, since sometimes this would be
# necessary even at present day (for example to resolve topological polygons)
# Torsvik's Rotation file: 520-0 Ma
rotation_model = pygplates.RotationModel('CEED_ROTATION_ENGINE_CHLOE.rot')

# partition features
partitioned_geometries = pygplates.partition_into_plates(static_polygons,
                                                       rotation_model,
                                                       input_geometries,
                                                       partition_method = pygplates.PartitionMethod.most_overlapping_plate)

# Write the partitioned data set to a file
output_feature_collection = pygplates.FeatureCollection(partitioned_geometries)
output_feature_collection.write('thai_partitioned.gpml')



# Reconstruct features to age
age = 246

# Reconstruct the geometries

pygplates.reconstruct(partitioned_geometries,
                      rotation_model,
                      'reconstructed_geom.gmt',
                      age, anchor_plate_id=1)  

pygplates.reconstruct(static_polygons,
                      rotation_model,
                      'reconstructed_CEED_land_simple.gmt',
                     age, anchor_plate_id=1) 

pygplates.reconstruct(exposed_Land,
                      rotation_model,
                      'reconstructed_CEED_Exposed_Land.gmt',
                     age, anchor_plate_id=1) 





# Plot using pyGMT
import os
import pygmt
import itertools as it


def extract():
    filename = 'reconstructed_geom.gmt'
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
edge_info = extract()
central_lon= (edge_info[0]+edge_info[1])/2
central_lat= (edge_info[2]+edge_info[3])/2
projection_Panel_1 = "G"+ str(central_lon)+"/"+str(central_lat)+ "60/?"
projection_Panel_2 = "W" + str(central_lon) + "/?"


age_label = str(age) + ' Ma'


fig = pygmt.Figure()
with fig.subplot(nrows=1, ncols=2, figsize=("19c", "7c"), frame="lrtb", autolabel=True,margins=["0.0c", "0.0c"],title = str(age_label),FONT_HEADING=12 ):
    with fig.set_panel(panel=[0,0]):
        # plotting panel b
        
        fig.coast (region="d",projection=projection_Panel_1,land="skyblue",water="skyblue", panel=[0, 0]) 
        fig.plot(data = 'reconstructed_CEED_land_simple.gmt',G="skyblue2",pen="0.1p,black", panel=[0, 0],)
        fig.plot(data = 'reconstructed_CEED_Exposed_Land.gmt',G="bisque1@20",pen="0.1p,black", panel=[0, 0],)
    
        fig.plot(data = 'reconstructed_geom.gmt',pen="0.25p,black",color="255/240/161", panel=[0, 0]) 
        fig.basemap(frame="g30")
        
        # plotting panel b
        fig.coast (region="d",projection=projection_Panel_2, land="skyblue",water="skyblue", panel=[0, 1])
        fig.plot(data = 'reconstructed_CEED_land_simple.gmt',G="skyblue2",pen="0.1p,black", panel=[0, 1],)
        fig.plot(data = 'reconstructed_CEED_Exposed_Land.gmt',G="bisque1@20",pen="0.1p,black", panel=[0, 1],)
     
        fig.plot(data = 'reconstructed_geom.gmt',pen="0.25p,black",color="255/240/161", panel=[0, 1])    
        fig.basemap(frame="a30g30")
        # LABELING

        filename = "reconstructed_geom.gmt"

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
    

    
fig.savefig("CEED2021_Orthographic_Mollweide.png",dpi="1080")

fig.show()

