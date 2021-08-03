#!/usr/bin/env python3
# -*- coding: utf-8 -*-


#!pip3 install pygmt
import sys
sys.path.insert(1, '/usr/lib/pygplates/revision28')
import pygplates
import pandas as pd
print("hello1")
# The geometries are the 'features to partition'
input_geometries = pygplates.FeatureCollection('thai.geojson')

print("step0")
# static polygons are the 'partitioning features'
static_polygons = pygplates.FeatureCollection('shapes_static_polygons_Merdith_et_al.gpml')

print("step1")
coastlines = pygplates.FeatureCollection('shapes_coastlines_Merdith_et_al.gpml')

# The partition_into_plates function requires a rotation model, since sometimes this would be
# necessary even at present day (for example to resolve topological polygons)
rotation_model = pygplates.RotationModel('1000_0_rotfile_Merdith_et_al.rot')

# partition features
partitioned_geometries = pygplates.partition_into_plates(static_polygons,
                                                       rotation_model,
                                                       input_geometries,
                                                       partition_method = pygplates.PartitionMethod.most_overlapping_plate)

# Write the partitioned data set to a file
output_feature_collection = pygplates.FeatureCollection(partitioned_geometries)
output_feature_collection.write('thai_partitioned.gpml')
print("stepcomplete")
#argument = int(sys.argv[1])
age = 200

print("hellopygplates")
# Reconstruct the geometries

print(static_polygons)
print(rotation_model)
print(age)
pygplates.reconstruct(static_polygons,
                      rotation_model,
                      'reconstructed_static_polygons.gmt',
                     age) 
print("firstReconstruct")
pygplates.reconstruct(coastlines,
                      rotation_model,
                      'reconstructed_coastlines.gmt',
                     age) 

pygplates.reconstruct(partitioned_geometries,
                      rotation_model,
                      'reconstructed_geom.gmt',
                      age)  
print("hellopygmt")
#import pygmt
try:
    import pygmt
except Exception as Ex:
    print(Ex)
print("pygmtPrinted")
import itertools as it
print("pygmtstarted")

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


age_label = str(age) + ' Ma'

fig = pygmt.Figure()

# Make a regional Mercator map with grid interval=30 degree; land and water= skyblue create a blue basemap. 
# Otherwise, if use the basemap format, the base-color cannot be filled with blue.
edge_info = extract()
region_edge = str(edge_info[0]-60) + "/" + str(edge_info[1]+60) + "/" + str(edge_info[2]-20) + "/" + str(edge_info[3]+20)
fig.coast (region=region_edge, projection="M15c", frame="afg30",land="skyblue",water="skyblue") #
# fig.basemap(region="50/150/-50/50", projection="M15c", frame="afg30") # xmin/xmax/ymin/ymax, 

#plot reconstruction polygons and coastlines filled with white(seashell) color,G="seashell" pen="0.1p,black"
fig.plot(data = 'reconstructed_static_polygons.gmt',G="seashell",pen="0.1p,black")
fig.plot(data = 'reconstructed_coastlines.gmt',G="seashell",pen="0.1p,black")
fig.plot(data = 'reconstructed_geom.gmt',pen="1p,red")

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
    fig.text(text=info[3], x=float(co_or[0]), y=float(co_or[1]), N=True, D="+j0.0/0.3c", font="7p,Helvetica-Bold,black")
    
# print(output)



# WITH FRONTS - fig.plot(data = 'reconstructed_geom.gmt',pen="1p,red",style="f1c/0.25c")

with fig.inset(position="jTR+w4c", box="+pblack+gwhite"):
    # Use a plotting function to create a figure inside the inset
    # Make a global Robinson map (or any other projection that you like) filled with blue color and grid every 30 degree.
  fig.coast (region="g", projection="R?", frame="g30",land="skyblue",water="skyblue")
   #plot reconstructed polygons and coastlines onto the inset map
  fig.plot(data = 'reconstructed_static_polygons.gmt',G="seashell",pen="0.1p,black")
  fig.plot(data = 'reconstructed_coastlines.gmt',G="seashell",pen="0.1p,black")
  fig.plot(data = 'reconstructed_geom.gmt',  color = "blue", pen="1p,red")
  # place reconstruction age in the inset map : fig.text(text="TEST", x=lon, y=lat, font="22p,Helvetica-Bold,black")
  #somehow, X=180, Y=45 is an ideal position to place the text.
  fig.text(text=age_label, x=180, y=45, N=True, D="+j0/1c", font="12p,Helvetica-Bold,black")  
  
print("Hello")
fig.savefig("my-figure_2.png") # Do you need some kind of unique filename to prevent conflicts if multiple users are online?
#fig.show()
