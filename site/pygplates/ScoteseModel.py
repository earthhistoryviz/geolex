#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys
sys.path.insert(1, '/usr/lib/pygplates/revision28')
import os
import csv
import pygplates
import pygmt
from Utils import (
    prepare_params,
    create_litho_dict,
    parse_GMT_file,
    plot_shapes_and_litho_patterns,
    label_shapes_with_names,
    calculate_edges,
    calculate_central_coor,
)
from Constants import (
    GEOJSON_FILE,
    GEOM_FILE,
    SCOTESE_STATIC_POLYGON_FILE,
    SCOTESE_ROT_MODEL_FILE,
    SCOTESE_DOC_DIR,
    SCOTESE_CPT_FILE,
    SCOTESE_AGE_DURATION_FILE,
    SCOTESE_DEFAULT_DEM_FILE,
    SCOTESE_DEM_DIR,
    SCOTESE_STATIC_POLYGON_OUTPUT,
)


def pygplate_reconstructions(corrected_age):
    # The geometries are the 'features to partition'
    input_geometries = pygplates.FeatureCollection(outdirname + GEOJSON_FILE)

    # Static polygons are the 'partitioning features'
    static_polygons = pygplates.FeatureCollection(SCOTESE_STATIC_POLYGON_FILE)

    # The partition_into_plates function requires a rotation model, since sometimes this would be
    #   necessary even at present day (for example to resolve topological polygons)
    rotation_model = pygplates.RotationModel(SCOTESE_ROT_MODEL_FILE)

    # Partition features
    partitioned_geometries = pygplates.partition_into_plates(static_polygons,
                                                             rotation_model,
                                                             input_geometries,
                                                             partition_method=pygplates.PartitionMethod.most_overlapping_plate)

    # Reconstruct geometries
    pygplates.reconstruct(static_polygons,
                          rotation_model,
                          outdirname + SCOTESE_STATIC_POLYGON_OUTPUT,
                          corrected_age)

    pygplates.reconstruct(partitioned_geometries,
                          rotation_model,
                          outdirname + GEOM_FILE,
                          corrected_age)


def get_gridfile(age):
    pygmt.makecpt(cmap=SCOTESE_DOC_DIR + SCOTESE_CPT_FILE)

    corrected_age = 0
    gridfile = ''
    
    if age == 0:
        gridfile = SCOTESE_DEFAULT_DEM_FILE 
        corrected_age = 0
    else:
        print(os.listdir("./config/Scotese"))
        with open(SCOTESE_DOC_DIR + SCOTESE_AGE_DURATION_FILE, 'r') as file:
            lookup = csv.reader(file)
            next(lookup, None) 
            for row in lookup:
                if row[0] == '':
                    break
                if age <= float(row[1]) and age >= float(row[2]):
                    gridfile = row[0].strip()
                    corrected_age = float(row[1])
                    break
    
    if gridfile == '':
        print('No reconstrution available for {}'.format(age))
        exit(1)

    return SCOTESE_DOC_DIR + SCOTESE_DEM_DIR + '/' + gridfile, corrected_age


if __name__ == '__main__':
    fig = pygmt.Figure()
    age, outdirname = prepare_params()
    age_label = str(age) + ' Ma'
    litho_dict = create_litho_dict()

    gridfile, corrected_age = get_gridfile(age)
    pygplate_reconstructions(corrected_age)
    pattern_list, name_list, coor_list = parse_GMT_file(outdirname)

    # Finding the edges and central view for creating the projection
    edge_coor, edge_label = calculate_edges(coor_list)
    central_lon, central_lat = calculate_central_coor(edge_coor)

    if abs(edge_coor[2]) > 35 or abs(edge_coor[3]) > 35: # if the boundary of map reaches beyond 55 degress north or south in latitude.
        if abs(edge_coor[2] - edge_coor[3]) < 65: # And, if all polygons are in the same hemisphere, plot polar projection. 
            # Plot base grid map (Scotese's DEM). The eye view is centered based on the center of all polygons. '60' is a horizon parameter.
            projection = 'G' + str(central_lon) + '/' + str(central_lat) + '/60/7.5c'
            fig.grdimage(grid=gridfile, region='d', projection= projection, shading='+d', frame=['a30g30', 'NE'])

            plot_shapes_and_litho_patterns(pattern_list, litho_dict, coor_list, fig)

            file_path = outdirname + "/region_and_projection.txt"
            with open(file_path, 'w') as file:
                file.write("Projection: " + projection + "\n")
                file.write("Region: d" + "\n")
                file.write("Age: " + age_label + "\n")
                file.write("Map Type: Polar")
            fig.colorbar(frame=['xa4000f1000+lElevation', 'y+lm']) # Set the bottom Color-Elevation bar to either plot.

        else: # If the boundary of map is beyongd 55 degress north or south in latitude (near poles), but all polygons are NOT in the same hemisphere, plot global projection.
            projection = 'W' + str(central_lon) + '/15c'
            fig.grdimage(grid=gridfile, region='d', projection=projection, shading='+d', frame=['a30g30', 'NE']) 

            plot_shapes_and_litho_patterns(pattern_list, litho_dict, coor_list, fig)

            file_path = outdirname + "/region_and_projection.txt"
            with open(file_path, 'w') as file:
                file.write("Projection: " + projection + "\n")
                file.write("Region: d" + "\n")
                file.write("Age: " + age_label + "\n")
                file.write("Map Type: Mollweide")
            fig.colorbar(frame=['xa2000f500+lElevation', 'y+lm']) # set the bottom Color-Elevation bar to either plot.

        fig.text(text=age_label, x=180, y=-90, N=True, D='5/0c', font='12p,Helvetica-Bold,black') # A reconstruction age stamp to the projection
    
    else: # When the boundary of map is within 55 degress north and south in latitude, or say close to the equator, plot regional map projection.
        fig.grdimage(grid=gridfile, region=edge_label, projection='M15c', shading='+d', frame=['afg30', 'NE']) #plot base grid map (Scotese' DEM).
        plot_shapes_and_litho_patterns(pattern_list, litho_dict, coor_list, fig)
        with fig.inset(position='jTR+w4c', box='+pblack+gwhite'): # create a corner figure inside the inset
            fig.grdimage(grid=gridfile, region='g', projection='R' + str(central_lon) + '/?')
            #Plot reconstructed polygons and coastlines onto the inset map
            fig.plot(data=outdirname + GEOM_FILE, color='red')
            fig.plot(data=outdirname + GEOM_FILE, color='red', frame='g30')

            # Place reconstruction age in the inset map : fig.text(text='TEST', position='TC', font='22p,Helvetica-Bold,black')
            #   somehow, position='TC' means that it is in the Top central of the plotted earth (inset), we must drift 0/0.5c outside of the globe as this is an ideal position to place the text.
            fig.text(text=age_label, position='TC', D='0/0.5c', N=True, font='12p,Helvetica-Bold,black')  
            fig.colorbar(frame=['xa2000f500+lElevation', 'y+lm']) # set the bottom Color-Elevation bar to either plot.

        file_path = outdirname + "/region_and_projection.txt"
        with open(file_path, 'w') as file:
            file.write("Projection: M15c" + "\n")
            file.write("Region: " + edge_label + "\n")
            file.write("Age: " + age_label + "\n")
            file.write("Map Type: Rectangular")

    if len(pattern_list) <= 1:
        label_shapes_with_names(name_list, coor_list, fig)
    
    fig.savefig(outdirname + '/final_image.png', dpi='300')
