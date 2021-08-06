#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Mon May 17 12:00:17 2021

@author: suyash
"""
#!pip3 install pygmt
import sys
sys.path.insert(1, '/usr/lib/pygplates/revision28')
import pygplates
import pandas as pd
# The geometries are the 'features to partition'

# Command-line params:
age = float(sys.argv[1])
outdirname = sys.argv[2]

print("Before")

try:
    input_geometries = pygplates.FeatureCollection(outdirname+'/recon.geojson')
except Exception as e:
    print(e)
# static polygons are the 'partitioning features'
static_polygons = pygplates.FeatureCollection('./config/shapes_static_polygons_Merdith_et_al.gpml')

coastlines = pygplates.FeatureCollection('./config/Global_EarthByte_GPlates_PresentDay_ContinentalPolygons_2019_v1.shp')
# The partition_into_plates function requires a rotation model, since sometimes this would be
# necessary even at present day (for example to resolve topological polygons)
rotation_model = pygplates.RotationModel('./config/1000_0_rotfile_Merdith_et_al.rot')

# partition features
partitioned_geometries = pygplates.partition_into_plates(static_polygons,
                                                       rotation_model,
                                                       input_geometries,
                                                       partition_method = pygplates.PartitionMethod.most_overlapping_plate)

# Aaron: Commented these lines because they don't appear to be used
# Write the partitioned data set to a file
# output_feature_collection = pygplates.FeatureCollection(partitioned_geometries)
# output_feature_collection.write('data/thai_partitioned.gpml')

# Reconstruct the geometries
pygplates.reconstruct(static_polygons,
                      rotation_model,
                      outdirname+'/reconstructed_static_polygons.gmt',
                     age) 
pygplates.reconstruct(coastlines,
                      rotation_model,
                      outdirname+'/reconstructed_Global_EarthByte_GPlates_PresentDay_ContinentalPolygons_2019_v1.gmt',
                     age) 

pygplates.reconstruct(partitioned_geometries,
                      rotation_model,
                      outdirname+'/reconstructed_geom.gmt',
                      age)  
#import pygmt
try:
    import pygmt
except Exception as Ex:
    print(Ex)
import itertools as it

def extract(outdirname):
    try:
        filename = outdirname+'/reconstructed_geom.gmt'
    except Exception as Ex:
        print(Ex);
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
print("after creating fig")
# Make a regional Mercator map with grid interval=30 degree; land and water= skyblue create a blue basemap. 
# Otherwise, if use the basemap format, the base-color cannot be filled with blue.
print("Before function")
edge_info = extract(outdirname)
region_edge = str(edge_info[0]-60) + "/" + str(edge_info[1]+60) + "/" + str(edge_info[2]-20) + "/" + str(edge_info[3]+20)

print("done")

if edge_info[2] < -40 or edge_info[3] > 40:

    central= (edge_info[0]+edge_info[1])/2
    projection = "W" + str(central) + "/15c"
    fig.coast (region="d",projection=projection, frame="a30g30",land="skyblue",water="skyblue") #
    
else:
    fig.coast (region=region_edge, projection="M15c", frame="afg30",land="skyblue",water="skyblue") #

# fig.basemap(region="50/150/-50/50", projection="M15c", frame="afg30") # xmin/xmax/ymin/ymax, 

#plot reconstruction polygons and coastlines filled with white(seashell) color,G="seashell" pen="0.1p,black"

fig.plot(data = outdirname+'/reconstructed_Global_EarthByte_GPlates_PresentDay_ContinentalPolygons_2019_v1.gmt',G="seashell",pen="0.1p,black")
fig.plot(data = outdirname+'/reconstructed_geom.gmt',pen="0.25p,black",color="255/240/161",)
# LABELING

filename = outdirname+"/reconstructed_geom.gmt"

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
        fig.text(text=info[3], x=float(co_or[0]), y=float(co_or[1]), N=True, D="+j0.0/0.3c", font="7p,Helvetica-Bold,black")
except Exception as e:
    print(e)



# WITH FRONTS - fig.plot(data = 'reconstructed_geom.gmt',pen="1p,red",style="f1c/0.25c")

if edge_info[2] >= -40 and edge_info[3] <= 40:
    with fig.inset(position="jTR+w4c", box="+pblack+gwhite"):
    # Use a plotting function to create a figure inside the inset
    # Make a global Robinson map (or any other projection that you like) filled with blue color and grid every 30 degree.
        fig.coast (region="g", projection="R?", frame="g30",land="skyblue",water="skyblue")
   #plot reconstructed polygons and coastlines onto the inset map
        
        fig.plot(data = outdirname+'/reconstructed_Global_EarthByte_GPlates_PresentDay_ContinentalPolygons_2019_v1.gmt',G="seashell",pen="0.1p,black")
       # fig.plot(data = outdirname+'/reconstructed_geom.gmt',pen="1p,red")
  # place reconstruction age in the inset map : fig.text(text="TEST", x=lon, y=lat, font="22p,Helvetica-Bold,black")
  #somehow, X=180, Y=45 is an ideal position to place the text.
        fig.text(text=age_label, x=180, y=45, N=True, D="0/1c", font="12p,Helvetica-Bold,black")  

# WITH FRONTS - fig.plot(data = 'reconstructed_geom.gmt',pen="1p,red",style="f1c/0.25c")
else:
    fig.text(text=age_label, x=180, y=-90, N=True, D="5/0c", font="12p,Helvetica-Bold,black")   
fig.savefig(outdirname+"/final_image.png") # Do you need some kind of unique filename to prevent conflicts if multiple users are online?
#fig.show()
