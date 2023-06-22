#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
This file contains all the constants used in the reconstruction process.
"""


# Below are constants shared across all the models.
GEOJSON_FILE = '/recon.geojson'
GEOM_FILE = '/reconstructed_geom.gmt'
LITHO_COLOR_CODE_FILE = './config/TSCreator_litho-pattern_to_GMT-fixed_pattern_code.csv'

# Below are constants used by the Default Model.
DEFAULT_STATIC_POLYGON_FILE = './config/Default/shapes_static_polygons_Merdith_et_al.gpml'
DEFAULT_COASTLINE_FILE = './config/Default/shapes_continents_Merdith_et_al.gpml'
DEFAULT_ROT_MODEL_FILE = './config/Default/1000_0_rotfile_Merdith_et_al.rot'

DEFAULT_STATIC_POLYGON_OUTPUT = '/reconstructed_static_polygons.gmt'
DEFAULT_COASTLINE_OUTPUT = '/reconstructed_shapes_continents_Merdith_et_al.gmt'

# Below are constants used by the Marcilly Model.
MARCILLY_STATIC_POLYGON_FILE = './config/Marcilly/Land_Simple_CEED_2021.gpml'
MARCILLY_EXPOSED_LAND_FILE = './config/Marcilly/Exposed_Land_CEED_2021.gpml'
MARCILLY_TERRANE_SIMPLE_FILE = './config/Marcilly/Terranes_Simple_CEED2021.gpml'
MARCILLY_ROT_MODEL_FILE = './config/Marcilly/CEED_ROTATION_ENGINE_CHLOE.rot'

MARCILLY_STATIC_POLYGON_OUTPUT = '/reconstructed_CEED_land_simple.gmt'
MARCILLY_EXPOSED_LAND_OUTPUT = '/reconstructed_CEED_Exposed_Land.gmt'

# Below are constants used by the Scotese Model.
SCOTESE_STATIC_POLYGON_FILE = './config/Scotese/PALEOMAP_PlatePolygons.gpml'
SCOTESE_ROT_MODEL_FILE = './config/Scotese/PALEOMAP_PlateModel.rot'
SCOTESE_DOC_DIR = './config/Scotese'
SCOTESE_CPT_FILE = '/Scotese.cpt'
SCOTESE_AGE_DURATION_FILE = '/Age_Duration_Of_Scotese_DEM_High_Resolution_GST2020_V3_28Nov22.csv'
SCOTESE_DEFAULT_DEM_FILE = '/Map01_PALEOMAP_6min_Holocene_0Ma.nc'
SCOTESE_DEM_DIR = '/Scotese_DEM_high_resolution'

SCOTESE_STATIC_POLYGON_OUTPUT = '/reconstructed_static_polygons.gmt'
