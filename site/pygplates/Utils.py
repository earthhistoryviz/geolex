#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys
import pygmt
import csv
import numpy as np
from Constants import (
    RECON_GEOM_FILE_SUFFIX,
    PATTERN_COLOR_CODE_FILE,
)


def prepare_params():
    """
    This function reads command-line input
    """
    age = float(sys.argv[1])
    outdirname = sys.argv[2]

    return age, outdirname


def create_litho_dict():
    """
    This function creates a dictionary with key as the Lithology Pattern Name and values as the PyGMT/GMT color/pattern code.
    Name and patterns are read from the "TSCreator_litho-pattern_to_GMT-fixed_pattern_code.csv" file created by Wen Du and Professor Ogg's TSC pattern code to pattern color code for GMT.
    All keys are lowercase.
    """
    color_dict = {}
    try:
        with open(PATTERN_COLOR_CODE_FILE, 'r') as csv_file:
            csv_reader = csv.reader(csv_file)
            # Skip headers
            next(csv_reader)
            next(csv_reader)

            for line in csv_reader:
                color_dict[line[0].lower()] = line[2]
    except Exception as e:
        print(f'The error is {e}', file=sys.stderr)

    return color_dict


def parse_GMT_file(outdirname):
    """
    This function reads the "./reconstructed_geom.gmt" file to extract information.
    It will return:
        1) a list of all the lithology pattern names in the GMT file and their corresponding colors
        2) a list of the names of all formations
        3) a list of coordinates of all formations
    """
    with open(outdirname + RECON_GEOM_FILE_SUFFIX, 'r') as f:
        contents = f.read().split('>')[1:]

    pattern_list = []
    name_list = []
    coor_list = [[], []]
    for formation in contents:
        sections = formation.splitlines()
        # First line will be empty (trailing \n from >)
        info = sections[1].replace('\"', '').split('|')

        name_list.append(info[3])

        if len(info) == 5:
            pattern_list.append(info[4].lower())
        else:
            pattern_list.append('unknown')

        x_coor = []
        y_coor = []
        for line in sections[3:]:
            coords = line.rstrip().split(" ")
            x_coor.append(float(coords[0]))
            y_coor.append(float(coords[1]))
        coor_list[0].append(x_coor)
        coor_list[1].append(y_coor)

    return pattern_list, name_list, coor_list


def plotting_shapes_and_lithology(pattern_list, color_dict, coor_list, fig):
    """
    This function draws the shape of each reconstruction and fills the shape with the correct color according to its lithology pattern.
    """
    for pattern, x_arr, y_arr in zip(pattern_list, coor_list[0], coor_list[1]):
        color = color_dict[pattern]
        fig.plot(
            x=np.array(x_arr),
            y=np.array(y_arr),
            pen="0.25p,black",
            frame="afg30",
            color=color
        )


def labeling_shapes_with_names(name_list, coor_list, fig):
    """
    This function labels shapes with corresponding names.
    """
    for name, x_arr, y_arr in zip(name_list, coor_list[0], coor_list[1]):
        fig.text(
            text=name,
            x=x_arr[0],
            y=y_arr[0],
            N=True,
            D="0.0/0.3c",
            font="7p,Helvetica-Bold,black"
        )


def get_edges(coor_list):
    """
    This functions locates the most Eastern, Western, Southern, Northern point of all formations.
    """
    lon_list = [lon for longitudes in coor_list[0] for lon in longitudes]
    lat_list = [lat for latitudes in coor_list[1] for lat in latitudes]

    return [min(lon_list), max(lon_list), min(lat_list), max(lat_list)]
