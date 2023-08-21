#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys
sys.path.insert(1, '/usr/lib/pygplates/revision28')
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
    MARCILLY_STATIC_POLYGON_FILE,
    MARCILLY_EXPOSED_LAND_FILE,
    MARCILLY_TERRANE_SIMPLE_FILE,
    MARCILLY_ROT_MODEL_FILE,
    MARCILLY_STATIC_POLYGON_OUTPUT,
    MARCILLY_EXPOSED_LAND_OUTPUT,
)


def pygplate_reconstructions(outdirname):
    # The geometries are the 'features to partition'
    input_geometries = pygplates.FeatureCollection(outdirname + GEOJSON_FILE)
        
    # land_simple is the 'partitioning features', Land polygons of today
    static_polygons = pygplates.FeatureCollection(MARCILLY_STATIC_POLYGON_FILE)
    
    # Exposed land in 10 Myrs interval
    exposed_Land = pygplates.FeatureCollection(MARCILLY_EXPOSED_LAND_FILE)
    
    # Some terrane polygons
    terranes_simple = pygplates.FeatureCollection(MARCILLY_TERRANE_SIMPLE_FILE)
    
    # The partition_into_plates function requires a rotation model, since sometimes this would be
    # necessary even at present day (for example to resolve topological polygons)
    # Torsvik's Rotation file: 520-0 Ma
    rotation_model = pygplates.RotationModel(MARCILLY_ROT_MODEL_FILE)

    # partition features
    partitioned_geometries = pygplates.partition_into_plates(static_polygons, rotation_model, input_geometries, partition_method=pygplates.PartitionMethod.most_overlapping_plate)
    
    # Write the partitioned data set to a file
    pygplates.reconstruct(partitioned_geometries, rotation_model, outdirname + GEOM_FILE, age, anchor_plate_id=1)  
    pygplates.reconstruct(static_polygons, rotation_model, outdirname + MARCILLY_STATIC_POLYGON_OUTPUT, age, anchor_plate_id=1)
    pygplates.reconstruct(exposed_Land, rotation_model, outdirname + MARCILLY_EXPOSED_LAND_OUTPUT, age, anchor_plate_id=1)


def plot_inset(central_lon):
    with fig.inset(position='jTR+w4c', box='+pblack+gwhite'):
        # Make a global Robinson map filled with blue color and grid every 30 degree.
        fig.coast(region='g', projection='R' + str(central_lon) + '/?', frame='g30', land='skyblue', water='skyblue')

        # Plot reconstructed polygons and coastlines onto the inset map 
        fig.plot(data=outdirname + MARCILLY_STATIC_POLYGON_OUTPUT, G='skyblue2', pen='0.1p,black')
        fig.plot(data=outdirname + MARCILLY_EXPOSED_LAND_OUTPUT, G='bisque1@20', pen='0.1p,black')
        fig.plot(data=outdirname + GEOM_FILE, frame='g30', pen='1p,red')   
        
        # Place reconstruction age in the inset map
        #   somehow, X=180, Y=45 is an ideal position to place the text. 
        fig.text(text=age_label, x=180, y=90, N=True, D='-0.25/0.25c', font='12p,Helvetica-Bold,black') 


def plot_coast_and_land(outdirname, fig):
    fig.plot(data=outdirname + MARCILLY_STATIC_POLYGON_OUTPUT, G='skyblue2', pen='0.1p,black')
    fig.plot(data=outdirname + MARCILLY_EXPOSED_LAND_OUTPUT, G='bisque1@20', pen='0.1p,black')


if __name__ == '__main__':
    fig = pygmt.Figure()
    age, outdirname = prepare_params()
    age_label = str(age) + ' Ma'
    litho_dict = create_litho_dict()

    pygplate_reconstructions(outdirname)
    pattern_list, name_list, coor_list = parse_GMT_file(outdirname)

    # Finding the edges and central view for creating the projection
    edge_coor, edge_label = calculate_edges(coor_list)
    central_lon, central_lat = calculate_central_coor(edge_coor)

    if abs(edge_coor[2]) > 35 or abs(edge_coor[3]) > 35: # if the boundary of map reaches beyond 55 degrees (35 after some conversions) north or south in latitude.
        if abs(edge_coor[2] - edge_coor[3]) < 65: # And, if all polygons are in the same hemisphere, plot polar projection.
            projection = 'G' + str(central_lon) + '/' + str(central_lat) + '/60/7.5c'
            fig.coast(region='d', projection=projection, frame='a30g30', land='skyblue', water='skyblue')

            plot_coast_and_land(outdirname, fig)
            plot_shapes_and_litho_patterns(pattern_list, litho_dict, coor_list, fig, frame_text='a30g30')

        else: # if the boundary of map is beyongd 55 degrees north or south in latitude (near poles), but all polygons are NOT in the same hemisphere, plot global projection.
            projection = 'W' + str(central_lon) + '/15c'
            fig.coast(region='d', projection=projection, frame='a30g30', land='skyblue', water='skyblue')

            plot_coast_and_land(outdirname, fig)
            plot_shapes_and_litho_patterns(pattern_list, litho_dict, coor_list, fig, frame_text='a30g30')

        # Add a reconstruction age stamp to the projection
        fig.text(text=age_label, x=180, y=-90, N=True, D='5/0c', font='12p,Helvetica-Bold,black')

    else: # when the boundary of map is within 55 degrees north and south in latitude, or say close to the equator, plot regional map projection (rectangle).
        fig.coast(region=edge_label, projection='M15c', frame='afg30', land='skyblue', water='skyblue')

        plot_coast_and_land(outdirname, fig)
        plot_shapes_and_litho_patterns(pattern_list, litho_dict, coor_list, fig, frame_text='a30g30')
        plot_inset(central_lon)

    if len(pattern_list) <= 1:
        label_shapes_with_names(name_list, coor_list, fig)

    fig.savefig(outdirname + '/final_image.png', dpi='300') # TODO: Do you need some kind of unique filename to prevent conflicts if multiple users are online?
