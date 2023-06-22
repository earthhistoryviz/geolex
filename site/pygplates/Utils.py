#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys
import pygmt
import csv
import numpy as np
from Constants import (
    GEOM_FILE,
    LITHO_COLOR_CODE_FILE,
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
    litho_dict = {}
    
    try:
        with open(LITHO_COLOR_CODE_FILE, 'r') as csv_file:
            csv_reader = csv.reader(csv_file)
            # Skip headers
            next(csv_reader)
            next(csv_reader)

            for line in csv_reader:
                litho_dict[line[0].lower()] = line[2]
    except Exception as e:
        print(f'The error is {e}', file=sys.stderr)

    return litho_dict


def parse_GMT_file(outdirname):
    """
    This function reads the "./reconstructed_geom.gmt" file to extract information.
    It will return:
        1) a list of all the lithology pattern names in the GMT file and their corresponding colors
        2) a list of the names of all formations
        3) a list of coordinates of all formations
    """
    with open(outdirname + GEOM_FILE, 'r') as f:
        contents = f.read().split('>')[1:]

    pattern_list = []
    name_list = []
    coor_list = [[], []]
    prev_name = ''
    prev_pattern = ''
    for formation in contents:
        sections = formation.splitlines() # First line will be empty (trailing \n from >)
        data_line_begin = 3

        info = sections[1]
        if '|' in info:
            info = info.replace('\"', '').split('|')

            prev_name = info[3]
            name_list.append(prev_name)

            if len(info) >= 5:
                prev_pattern = info[4].lower()
            else:
                prev_pattern = 'unknown'
            pattern_list.append(prev_pattern)
        else:
            data_line_begin = 2
            name_list.append(prev_name)
            pattern_list.append(prev_pattern)

        x_coor = []
        y_coor = []
        for line in sections[data_line_begin:]:
            coords = line.rstrip().split(' ')
            x_coor.append(float(coords[0]))
            y_coor.append(float(coords[1]))
        coor_list[0].append(x_coor)
        coor_list[1].append(y_coor)

    return pattern_list, name_list, coor_list


def plot_shapes_and_litho_patterns(pattern_list, litho_dict, coor_list, fig, frame_text='afg30'):
    """
    This function draws the shape of each reconstruction and fills the shape with the correct color according to its lithology pattern.
    """
    for pattern, x_arr, y_arr in zip(pattern_list, coor_list[0], coor_list[1]):
        color = litho_dict[pattern] if pattern in litho_dict else litho_dict['unknown']
        fig.plot(
            x=np.array(x_arr),
            y=np.array(y_arr),
            pen='0.25p,black',
            frame=frame_text,
            color=color
        )


def label_shapes_with_names(name_list, coor_list, fig):
    """
    This function labels shapes with corresponding names.
    """
    for name, x_arr, y_arr in zip(name_list, coor_list[0], coor_list[1]):
        fig.text(
            text=name,
            x=x_arr[0],
            y=y_arr[0],
            N=True,
            D='0.0/0.3c',
            font='7p,Helvetica-Bold,black'
        )


def calculate_edges(coor_list):
    """
    This functions locates the Eastern-, Western-, Southern-, Northern-most points across all formations.
    """
    lon_list = [lon for longitudes in coor_list[0] for lon in longitudes]
    lat_list = [lat for latitudes in coor_list[1] for lat in latitudes]

    edge_coor = [min(lon_list), max(lon_list), min(lat_list), max(lat_list)]
    edge_label = str(edge_coor[0] - 60) + '/' + str(edge_coor[1] + 60) + '/' + str(edge_coor[2] - 20) + '/' + str(edge_coor[3] + 20)

    return edge_coor, edge_label


def calculate_central_coor(edges):
    """
    This function calculate the central latitude and longitude of across all formations.
    """
    central_lon = (edges[0] + edges[1]) / 2
    central_lat = (edges[2] + edges[3]) / 2

    return central_lon, central_lat
