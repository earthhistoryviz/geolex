#!/usr/bin/env python3
# -*- coding: utf-8 -*-

#!pip3 install pygmt
import sys
sys.path.insert(1, '/usr/lib/pygplates/revision28')
import pygplates
import pygmt
from Utils import (
    prepare_params,
    create_litho_dict,
    parse_GMT_file,
    plotting_shapes_and_lithology,
    labeling_shapes_with_names,
    get_edges,
)
from Constants import (
    RECON_GEOM_FILE_SUFFIX,
    GEOJSON_FILE_SUFFIX,
    RECON_STATIC_POLYGON_FILE_SUFFIX,
    RECON_COASTLINE_FILE_SUFFIX,
    STATIC_POLYGON_FILE,
    COASTLINE_FILE,
    ROT_MODEL_FILE,
)


def pygplate_reconstructions(outdirname):
    input_geometries = pygplates.FeatureCollection(outdirname + GEOJSON_FILE_SUFFIX)

    # static polygons are the 'partitioning features'
    static_polygons = pygplates.FeatureCollection(STATIC_POLYGON_FILE)

    coastlines = pygplates.FeatureCollection(COASTLINE_FILE)

    # The partition_into_plates function requires a rotation model, since sometimes this would be
    # necessary even at present day (for example to resolve topological polygons)
    rotation_model = pygplates.RotationModel(ROT_MODEL_FILE)

    # partition features
    partitioned_geometries = pygplates.partition_into_plates(static_polygons, rotation_model, input_geometries, partition_method=pygplates.PartitionMethod.most_overlapping_plate)

    pygplates.reconstruct(partitioned_geometries, rotation_model, outdirname + RECON_GEOM_FILE_SUFFIX, age)

    # Reconstruct the geometries
    pygplates.reconstruct(static_polygons, rotation_model, outdirname + RECON_STATIC_POLYGON_FILE_SUFFIX, age)
    pygplates.reconstruct(coastlines, rotation_model, outdirname + RECON_COASTLINE_FILE_SUFFIX, age)


def plotting_inset(central_lon):
    with fig.inset(position="jTR+w4c", box="+pblack+gwhite"):
        # Use a plotting function to create a figure inside the inset
        # Make a global Robinson map (or any other projection that you like) filled with blue color and grid every 30 degree.
        fig.coast(region="g", projection="R" + str(central_lon) + "/?", frame="g30", land="skyblue", water="skyblue")

        # plot reconstructed polygons and coastlines onto the inset map
        fig.plot(data=outdirname+RECON_COASTLINE_FILE_SUFFIX, G="seashell", pen="0.1p,black")
        fig.plot(data=outdirname+RECON_GEOM_FILE_SUFFIX, frame="g30", pen="1p,red")

        # place reconstruction age in the inset map: fig.text(text="TEST", position="TC", font="22p,Helvetica-Bold,black")
        # somehow, position="TC" means that it is in the Top central of the plotted earth (inset), we must drift 0/0.5c outside of the globe as this is an ideal position to place the text.
        fig.text(text=age_label, position="TC", D="0/0.5c", N=True, font="12p,Helvetica-Bold,black")


if __name__ == '__main__':
    fig = pygmt.Figure()
    age, outdirname = prepare_params()
    age_label = str(age) + ' Ma'
    color_dict = create_litho_dict()

    pygplate_reconstructions(outdirname)
    pattern_list, name_list, coor_list = parse_GMT_file(outdirname)
    edge_info = get_edges(coor_list)

    # Finding the central view for creating the projection
    central_lon = (edge_info[0] + edge_info[1]) / 2
    central_lat = (edge_info[2] + edge_info[3]) / 2
    region_edge = str(edge_info[0] - 60) + "/" + str(edge_info[1] + 60) + "/" + str(edge_info[2] - 20) + "/" + str(edge_info[3] + 20)

    if abs(edge_info[2]) > 35 or abs(edge_info[3]) > 35: # if the boundary of map reaches beyond 55 degrees (35 after some conversions) north or south in latitude.
        if abs(edge_info[2] - edge_info[3]) < 80: # And, if all polygons are in the same hemisphere, plot polar projection.
            projection = "G" + str(central_lon) + "/" + str(central_lat) + "/60/7.5c"
            fig.coast(region="d", projection=projection, frame="a30g30", land="skyblue", water="skyblue")
            fig.plot(data=outdirname + '/reconstructed_shapes_continents_Merdith_et_al.gmt', G="seashell", pen="0.1p,black")
            plotting_shapes_and_lithology(pattern_list, color_dict, coor_list, fig)

        else: # if the boundary of map is beyongd 55 degrees north or south in latitude (near poles), but all polygons are NOT in the same hemisphere, plot global projection.
            projection = "W" + str(central_lon) + "/15c"
            fig.coast(region="d", projection=projection, frame="a30g30", land="skyblue", water="skyblue")
            fig.plot(data=outdirname + '/reconstructed_shapes_continents_Merdith_et_al.gmt', G="seashell", pen="0.1p,black")
            plotting_shapes_and_lithology(pattern_list, color_dict, coor_list, fig)

        fig.text(text=age_label, x=180, y=-90, N=True, D="5/0c", font="12p,Helvetica-Bold,black") # A reconstruction age stamp to the projection

    else: # when the boundary of map is within 55 degrees north and south in latitude, or say close to the equator, plot regional map projection (rectangle).
        fig.coast(region=region_edge, projection="M15c", frame="afg30", land="skyblue", water="skyblue")
        fig.plot(data=outdirname + '/reconstructed_shapes_continents_Merdith_et_al.gmt', G="seashell", pen="0.1p,black")
        plotting_shapes_and_lithology(pattern_list, color_dict, coor_list, fig)
        plotting_inset(central_lon)

    if len(pattern_list) <= 3:
        labeling_shapes_with_names(name_list, coor_list, fig)

    fig.savefig(outdirname + "/final_image.png", dpi="300") # TODO: Do you need some kind of unique filename to prevent conflicts if multiple users are online?
