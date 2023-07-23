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
    DEFAULT_STATIC_POLYGON_OUTPUT,
    DEFAULT_COASTLINE_OUTPUT,
    DEFAULT_STATIC_POLYGON_FILE,
    DEFAULT_COASTLINE_FILE,
    DEFAULT_ROT_MODEL_FILE,
)


def pygplate_reconstructions(outdirname):
    input_geometries = pygplates.FeatureCollection(outdirname + GEOJSON_FILE)

    # static polygons are the 'partitioning features'
    static_polygons = pygplates.FeatureCollection(DEFAULT_STATIC_POLYGON_FILE)

    coastlines = pygplates.FeatureCollection(DEFAULT_COASTLINE_FILE)

    # The partition_into_plates function requires a rotation model, since sometimes this would be
    # necessary even at present day (for example to resolve topological polygons)
    rotation_model = pygplates.RotationModel(DEFAULT_ROT_MODEL_FILE)

    # partition features
    partitioned_geometries = pygplates.partition_into_plates(static_polygons, rotation_model, input_geometries, partition_method=pygplates.PartitionMethod.most_overlapping_plate)

    pygplates.reconstruct(partitioned_geometries, rotation_model, outdirname + GEOM_FILE, age)

    # Reconstruct the geometries
    pygplates.reconstruct(static_polygons, rotation_model, outdirname + DEFAULT_STATIC_POLYGON_OUTPUT, age)
    pygplates.reconstruct(coastlines, rotation_model, outdirname + DEFAULT_COASTLINE_OUTPUT, age)



def plotting_inset(central_lon, outdirname, fig):
    with fig.inset(position='jTR+w4c', box='+pblack+gwhite'):
        # Use a plotting function to create a figure inside the inset
        # Make a global Robinson map (or any other projection that you like) filled with blue color and grid every 30 degree.
        fig.coast(region='g', projection='R' + str(central_lon) + '/?', frame='g30', land='skyblue', water='skyblue')

        # plot reconstructed polygons and coastlines onto the inset map
        fig.plot(data=outdirname + DEFAULT_COASTLINE_OUTPUT, G='seashell', pen='0.1p,black')
        fig.plot(data=outdirname + GEOM_FILE, frame='g30', pen='1p,red')

        # place reconstruction age in the inset map: fig.text(text='TEST', position='TC', font='22p,Helvetica-Bold,black')
        # somehow, position='TC' means that it is in the Top central of the plotted earth (inset), we must drift 0/0.5c outside of the globe as this is an ideal position to place the text.
        fig.text(text=age_label, position='TC', D='0/0.5c', N=True, font='12p,Helvetica-Bold,black')


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
        if abs(edge_coor[2] - edge_coor[3]) < 80: # And, if all polygons are in the same hemisphere, plot polar projection.
            projection = 'G' + str(central_lon) + '/' + str(central_lat) + '/60/7.5c'
            fig.coast(region='d', projection=projection, frame='a30g30', land='skyblue', water='skyblue')
            fig.plot(data=outdirname + DEFAULT_COASTLINE_OUTPUT, G='seashell', pen='0.1p,black')
            plot_shapes_and_litho_patterns(pattern_list, litho_dict, coor_list, fig)

        else: # if the boundary of map is beyongd 55 degrees north or south in latitude (near poles), but all polygons are NOT in the same hemisphere, plot global projection.
            projection = 'W' + str(central_lon) + '/15c'
            fig.coast(region='d', projection=projection, frame='a30g30', land='skyblue', water='skyblue')
            fig.plot(data=outdirname + DEFAULT_COASTLINE_OUTPUT, G='seashell', pen='0.1p,black')
            plot_shapes_and_litho_patterns(pattern_list, litho_dict, coor_list, fig)

        fig.text(text=age_label, x=180, y=-90, N=True, D='5/0c', font='12p,Helvetica-Bold,black') # A reconstruction age stamp to the projection

    else: # when the boundary of map is within 55 degrees north and south in latitude, or say close to the equator, plot regional map projection (rectangle).
        fig.coast(region=edge_label, projection='M15c', frame='afg30', land='skyblue', water='skyblue')
        fig.plot(data=outdirname + DEFAULT_COASTLINE_OUTPUT, G='seashell', pen='0.1p,black')
        plot_shapes_and_litho_patterns(pattern_list, litho_dict, coor_list, fig)
        plotting_inset(central_lon, outdirname, fig)

    if len(pattern_list) <= 1:
        label_shapes_with_names(name_list, coor_list, fig)

    fig.savefig(outdirname + '/final_image.png', dpi='300')
