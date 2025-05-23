<?php
/*
 * Copy this file to Period_China_Map.php, then change the .jpg filename below and fix coords for your new image.
 * This file is the main one on the homepage of the site.
 * Name the file as: Order_Period_Color_whatever_Country_Map.php
 *   for example: 1_Quaternary_FF3322_China_Country_Map.php
 */

$page = "allformations_new";
include("../formationheader.php");
?>

<!--
In the below code, you need to change <h2> and </h2> to <div> and </div>
if they are lots of white spaces(border) in the image uploaded
-->
<h2><img src="Mapinfo/Devonian_China_provinces.jpg" width="800" height="625" border="0" usemap="#Map" />
  <map name="Map" id="Map">
    <area shape="poly" id="Xinjiang" coords="247,65,328,187,276,219,273,243,235,253,246,276,226,285,194,275,149,281,111,267,87,277,63,275,38,175,181,88" href="#Devonian_Xinjiang"/>
    <area shape="poly" id="Xizang" coords="219,287,192,280,151,287,109,272,44,289,104,375,179,428,231,454,310,442,331,418,327,385,316,363,298,378,263,360,217,332,220,295" href="#Devonian_Xizang" />
    <area shape="poly" id="Gansu" coords="328,191,305,210,280,224,279,242,321,264,330,252,392,290,398,309,392,316,385,324,379,332,381,344,394,341,412,359,420,368,430,365,435,350,448,344,441,330,468,327,468,307,446,296,442,308,446,317,439,326,424,317,426,301,421,287,411,289,399,282,415,263,411,256,386,267,358,244,367,231,360,227,347,229,334,217,340,210,336,191" href="#Devonian_Gansu" />
    <area shape="poly" id="Qinghai" coords="315,360,319,339,335,343,349,364,362,369,381,348,376,337,395,309,388,292,330,255,320,267,274,244,238,253,246,276,223,286,221,330,297,375" href="#Devonian_Qinghai" />
    <area shape="poly" id="Sichuan" coords="407,434,391,449,381,459,384,473,366,476,363,464,354,444,344,443,331,425,333,397,328,374,320,360,321,343,333,345,345,367,361,370,382,350,394,344,417,367,431,367,453,376,471,382,457,401,443,415,428,408,418,421,427,429" href="#Devonian_Sichuan" />
    <area shape="poly" id="Yunnan" coords="327,426,345,447,352,449,362,477,384,474,384,458,401,440,407,449,418,453,413,459,395,455,395,469,404,474,401,484,406,491,405,503,428,516,412,523,398,531,355,558,328,554,297,505,315,437" href="wells/Devonian_Yunnan.php" />
    <area shape="poly" id="Guizhou" coords="398,457,398,468,408,474,405,484,409,493,408,501,415,495,429,500,445,489,456,495,479,482,478,464,470,462,477,454,475,442,468,446,458,432,443,438,428,441,434,450,417,450,413,464" href="wells/Devonian_Guizhou.php" />
    <area shape="poly" id="Guangxi" coords="414,497,411,504,427,514,421,525,454,558,482,553,503,535,515,502,500,497,508,485,503,476,480,485,457,500,444,493,431,504" href="wells/Devonian_Guangxi.php" />
    <area shape="poly" id="Guangdong" coords="553,490,533,490,530,501,516,503,506,534,484,555,486,573,596,523,586,503,573,503,555,507" href="wells/Devonian_Fujian_Guangdong_Jiangxi.php" />
    <area shape="poly" id="Hunan" coords="481,479,498,473,510,484,504,496,514,500,525,499,528,487,538,487,543,474,536,456,544,442,539,431,533,423,518,425,501,417,492,423,481,423,476,440" href="wells/Devonian_Hunan.php" />
    <area shape="poly" id="Hainan" coords="485,577,466,588,462,604,495,608,506,596,506,578" href="#Devonian_Hainan" />
    <area shape="poly" id="Fujian" coords="636,453,616,453,608,439,588,458,576,495,589,502,597,519,634,483" href="wells/Devonian_Fujian_Guangdong_Jiangxi.php" />
    <area shape="poly" id="Hubei" coords="476,429,468,406,488,400,482,388,483,375,490,372,490,363,499,363,511,376,537,378,557,391,568,397,570,415,543,428,532,421,516,422,503,415,490,419,479,421" href="#Devonian_Hubei" />
    <area shape="poly" id="Chongqing" coords="478,387,471,385,444,416,429,411,420,421,431,428,405,438,412,447,428,446,426,438,440,435,454,429,469,441,472,432,462,406,481,397" href="#Devonian_Chongqing" />
    <area shape="poly" id="Jiangxi" coords="571,419,544,430,548,443,540,457,546,473,545,486,556,487,558,503,575,499,583,459,605,436,601,420" href="wells/Devonian_Fujian_Guangdong_Jiangxi.php" />
    <area shape="poly" id="Ningxia" coords="444,259,433,275,434,285,426,291,426,315,436,323,442,317,438,306,443,296,454,285,439,279" href="#Devonian_Ningxia" />
    <area shape="poly" id="Shaanxi" coords="501,260,488,262,473,285,466,291,458,287,452,295,472,306,470,327,445,332,452,344,433,363,455,372,478,383,484,369,489,359,496,358,486,340,489,305,490,280" href="#Devonian_Shaanxi" />
    <area shape="poly" id="Shanxi" coords="534,239,519,247,503,260,496,290,491,332,492,339,513,331,529,326,535,309,528,300,535,287,528,275,537,260" href="#Devonian_Shanxi" />
    <area shape="poly" id="Henan" coords="563,391,568,377,557,369,563,348,576,347,567,337,552,333,558,314,536,309,532,328,491,342,513,373,537,375" href="#Devonian_Henan" />
    <area shape="poly" id="Anhui" coords="580,344,572,351,565,351,560,365,572,376,567,391,573,412,602,417,619,392,602,390,599,380,602,369,593,362,590,351" href="#Devonian_Anhui" />
    <area shape="poly" id="Taiwan" coords="655,479,635,514,649,544,669,517,665,482" href="#Devonian_Taiwan" />
    <area shape="poly" id="Zhejiang" coords="642,396,636,391,622,392,603,421,618,449,639,451,657,424,659,399" href="#Devonian_Zhejiang" />
    <area shape="poly" id="Jiangsu" coords="608,325,594,336,573,337,594,349,604,366,604,386,658,394,639,351" href="#Devonian_Jiangsu" />
    <area shape="poly" id="Tianjin" coords="579,246,570,254,571,269,583,271,595,260" href="#Devonian_Tianjin" />
    <area shape="poly" id="Hebei" coords="583,271,586,278,557,310,534,301,541,287,534,274,542,256,540,241,537,224,545,215,553,222,577,208,586,222,598,224,592,233,608,245,595,258,579,244,567,252,567,270" href="#Devonian_Hebei" />
    <area shape="poly" id="Shandong" coords="589,277,561,310,556,330,574,333,594,333,605,322,653,287,622,278,605,288" href="#Devonian_Shandong" />
    <area shape="poly" id="Liaoning" coords="611,242,596,232,602,223,602,209,610,215,649,185,678,220,633,261,622,250,639,232,623,232" href="#Devonian_Liaoning" />
    <area shape="poly" id="Jilin" coords="680,216,648,182,619,143,640,137,684,157,734,168,713,207" href="#Devonian_Jilin" />
    <area shape="poly" id="Heilongjiang" coords="602,27,640,22,668,60,727,85,759,72,755,137,733,145,739,165,687,152,645,134,634,132,623,123,647,96,653,64,651,53,634,55,615,48" href="#Devonian_Heilongjiang" />
    <area shape="poly" id="Inner Mongolia" coords="341,191,344,210,340,219,348,228,361,226,369,230,363,244,388,263,409,253,417,260,406,281,419,284,431,282,429,271,443,254,449,259,444,276,465,284,483,258,495,255,506,251,534,235,534,221,543,212,552,213,564,211,580,204,596,217,598,204,611,209,642,182,615,145,628,133,618,122,644,93,649,56,629,56,610,51,600,32,591,24,554,82,547,118,547,130,575,123,593,130,545,147,525,160,495,170,501,185,478,203,425,211" href="#Devonian_Inner Mongolia" />
  </map>
</h2>

<?php
include("Mouseover_Text.php");
?>
