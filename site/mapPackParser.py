#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import csv
import json
import sys
import re

mapPackPath = sys.argv[1]
dataPackPath = sys.argv[2]

class Formation():
    def __init__(self, name, begin_date, end_date, lithology_pattern, formation_description, column, longitude, latitude, province):
        self.column = fixColumnName(column)
        self.name = fixName(name, self.column)
        self.begin_date = begin_date
        self.end_date = end_date
        self.lithology_pattern = lithology_pattern
        self.formation_description = formation_description
        self.province = province
        self.latitude = latitude
        self.longitude = longitude 

#Encodes the formation class to json
class FormationEncoder(json.JSONEncoder):
    def default(self, o):
        return o.__dict__

def get_province(province_dict, val):

    for province, list_of_columns in province_dict.items():
        if val in list_of_columns: #value is a list of column names
            return province

def fixColumnName(column):
    column = column.replace("-", " ")
    column = column.replace("_", " ")
    regex = re.compile("[^a-zA-Z?& ']")
    #First parameter is the replacement, second parameter is your input string
    return (regex.sub('', column)).title()



def fixName(name, column):
    regex = re.compile("[^a-zA-Z?& ']")
    #First parameter is the replacement, second parameter is your input string
    name = (regex.sub('', name)).title()
    namelist = name.split()
    
    namelist.insert( len(namelist)-1, column)
    return ' '.join(namelist)

#reads the datapack and returns a json 
def reading_datapack(columns_dict):
    try:
        list_of_formation = []
        last_age = -1
        current_column = ""
        state = "NOT_IN_COLUMN"
        lon_lat = (0,0)
        province_dict = {}
        current_prov = ""

        str2 = dataPackPath[len(dataPackPath) - 3:] #see if the ending of the file

        if str2 == '.csv': #if file is a .csv file
            try:
                with open(dataPackPath, newline='' ) as csvfile:
                    print("here")
                    data_file= list(csv.reader(csvfile, delimiter = ',')) #converts the read data file into a list for easy parsing.
            except UnicodeDecodeError: #encoding is in UTF-16 instead
                with open(dataPackPath, newline='', encoding='utf-16') as csvfile:
                    data_file= list(csv.reader(csvfile, delimiter = ',')) #converts the read data file into a list for easy parsing.
            
        else: #not .csv file
            try:
                with open(dataPackPath, newline='') as csvfile:
                    data_file= list(csv.reader(csvfile, delimiter = '\t')) #converts the read data file into a list for easy parsing.
            except UnicodeDecodeError: #encoding is in UTF-16 instead
                with open(dataPackPath, newline='', encoding='utf-16') as csvfile:
                    data_file= list(csv.reader(csvfile, delimiter = '\t')) #converts the read data file into a list for easy parsing.
                    

        #parsing the file and creating a list of objects
        for line in data_file:
            try:
                if(len(line) < 10):
                    line += [''] * (10 - len(line))

                if(state == 'NOT_IN_COLUMN'):
                    if(line[1] == 'facies'):
                        state = 'READING_FACIES'
                        current_column = line[0]
                        current_prov = get_province(province_dict, current_column)
                        lon_lat = columns_dict[current_column]
                        continue
                    
                    else: 
                        province_values = []
                        if line[0] != '':
                            for index in line:
                                if index != '' and index != ':' and index != line[0]:
                                    province_values.append(index)

                            province_dict[line[0]] = province_values
                    
                if(state == 'READING_FACIES'):
                    if all('' == s or s.isspace() for s in line):
                        state = 'NOT_IN_COLUMN'
                        continue
                    if(line[2] != '' and line[2] != ' '):
                        try:
                            formation = Formation(line[2], float(line[3]), last_age, line[1],line[4], current_column, lon_lat[0], lon_lat[1], current_prov)
                            list_of_formation.append(formation)
                        except:
                            print("FILE IS NOT VALID!")

                    if(line[3] != ''):
                        last_age = float(line[3])
            except KeyError:
                print('No ' + line[0] + 'in Mappack') #column might not exist in the MapPack so no coordinates
                continue

        return list_of_formation

    except Exception as e:
        print("INVALID data PACK!!!!")
        return 1

    #Debugging info
    # for formation in list_of_formation:
    #     print("Lithology: ", formation.lithology_pattern, "Name: ", formation.name, "begin Date:", formation.begin_date, "End Date: ", formation.end_date, "lat_lon", formation.lat_lon)

    


def reading_MapLocations():
        index = 0
        columns_dict = {} #dictionary that has the column's name as keys the values is a tuple of the column's lat and lon for the formation
        start = False

        #For example:
        #index 0: lat and long of the column as values, the first value will be a latitude the second value will be a longitude 
        #Belgium_TSCLiteMapLocations_12Dec2012.txt
        try:
            with open(mapPackPath, newline='') as csvfile:
                data = list(csv.reader(csvfile, delimiter = '\t'))
                for row in data:
                    index += 1
                    if(len(row) == 0):
                        continue
                    if(start): #this is because there are other header information in the file, this index is where the column names come in
                        #column_dict key are the column names
                        #column_dict values is a list of the column information 
                        #row[2] = LAT row[3] = LON
                        try:
                            columns_dict[row[1]] = (float(row[3]), float(row[2])) #adding lon and lat as a tuple 
                        except ValueError:
                            continue
                    if(row[1] == 'NAME'):
                        start = True

        except UnicodeDecodeError: #encoding is in UTF-16 instead
            with open(mapPackPath, newline='', encoding='utf-16') as csvfile:
                data = list(csv.reader(csvfile, delimiter = '\t'))
                for row in data:
                    index += 1
                    if(len(row) == 0):
                        continue
                    if(start): #this is because there are other header information in the file, this index is where the col names come in

                        #col_dict key are the col names
                        #col_dict values is a list of the col information 
                        #row[2] = LAT row[3] = LON
                        
                        try:
                            columns_dict[row[1]] = (float(row[3]), float(row[2])) #adding lon and lat as a tuple 
                        except ValueError:
                            continue

                    if(row[1] == 'NAME'):
                        start = True
    
        except Exception as e:
            print("INVALID MAP PACK!!!!")
            return 1
        
        return columns_dict

def main():
    columns_dict = reading_MapLocations() #reads file number 1 and returns a dictionary that has the column's name as keys the values is a tuple of the column's lat and lon for the formation
    list_of_formations = reading_datapack(columns_dict) #reads file number 2 and returns a list of object Formation
    
    index = 0
    string = ""
    formationFileName = "./uploads1/" +(str(sys.argv[3])[0:8]).replace(" ", "") +"_formations.json"


    with open(formationFileName, "w") as file1:
        # Writing data to a file
        file1.write("[\n")

        for formation in list_of_formations:
            
            string = json.dumps(formation, indent=4, cls=FormationEncoder)
            file1.write(string)
            index += 1
            if(index != len(list_of_formations)):
                file1.write(",")
            

        file1.write("]")
        
        


main()