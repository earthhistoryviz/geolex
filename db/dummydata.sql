USE testDB IF EXISTS;
TRUNCATE TABLE timeperiod;
TRUNCATE TABLE formation;
TRUNCATE TABLE wells;

INSERT INTO timeperiod(name,color)
VALUES
(	'Devonian',
	'203/140/55'
);

INSERT INTO timeperiod(name,color)
VALUES
(	'Quaternary',
	'249/249/127'
);

INSERT INTO timeperiod(name,color)
VALUES
(	'Neogene',
	'255/230/25'
);

INSERT INTO formation(name,period,age_interval,province,type_locality,lithology,lower_contact,
upper_contact,regional_extent,fossils,age,depositional,additional_info,compiler)
VALUES
(
	'A’ertaxi Gr',
	'Devonian',
	'D22 (15); Givetian (late Middle Devonian)',
	'Xinjiang',
	'The type section is located at south of A’ertaxi Village, north side of Kunlun Mts. in the Xinjiang Uygur Autonomous Region. . It was named by No 13 Geological Team of Xinjiang in 1957 and was published by Editorial Board of Xinjiang Regional Stratigraphical Scale (1980).',
	'Limestone, shale. The lower part of the Group is dominated by light-gray, dark-gray limestone and clayey shale, containing coral fossils. The upper part is characterized by green and black shale and dark-gray limestone with breccia limestone on its top. The thickness is 870 m. In the high mountain area between Longle-Agar River valley and Genlishalihe River, the group is characterized by gray, light greenish-gray quartzose sandstone, 260 to 900 m thick',
	'Unknown: The contact relationships to the underlying strata are not yet clear.',
	'Unknown: The contact relationships to the overlying strata are not yet clear.',
	' ',
	'Coral fossils: Eudophyllum sp., Brariphyllum sp., Syringopora sp., Temnophyllum sp.',
	'Givetian (late Middle Devonian)',
	' ',
	' ',
	'Wang Shitao'
);

INSERT INTO formation(name,period,age_interval,province,type_locality,lithology,lower_contact,
upper_contact,regional_extent,fossils,age,depositional,additional_info,compiler)
VALUES
(
	'A’ertaxi Gr',
	'Devonian',
	'D22 (15); Givetian (late Middle Devonian)',
	'Xinjiang',
	'The type section is located at south of A’ertaxi Village, north side of Kunlun Mts. in the Xinjiang Uygur Autonomous Region. . It was named by No 13 Geological Team of Xinjiang in 1957 and was published by Editorial Board of Xinjiang Regional Stratigraphical Scale (1980).',
	'Limestone, shale. The lower part of the Group is dominated by light-gray, dark-gray limestone and clayey shale, containing coral fossils. The upper part is characterized by green and black shale and dark-gray limestone with breccia limestone on its top. The thickness is 870 m. In the high mountain area between Longle-Agar River valley and Genlishalihe River, the group is characterized by gray, light greenish-gray quartzose sandstone, 260 to 900 m thick',
	'Unknown: The contact relationships to the underlying strata are not yet clear.',
	'Unknown: The contact relationships to the overlying strata are not yet clear.',
	' ',
	'Coral fossils: Eudophyllum sp., Brariphyllum sp., Syringopora sp., Temnophyllum sp.',
	'Givetian (late Middle Devonian)',
	' ',
	' ',
	'Wang Shitao2'
);	
