#!/usr/bin/env python3
# -*- coding: utf-8 -*-
#import matplotlib;
import hashlib
print("Hello world.");
print("This really is working.");
thing = "200_All_2021_05_25_07:29:30pm"
hexVersion = hashlib.md5(thing.encode())
print(hexVersion.hexdigest())
