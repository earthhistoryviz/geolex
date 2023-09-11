# API Guide

## General Format

Generally, because every region has its own database, an API call is only going to query data in a specific region. The overall structure of our APIs is `<server-url>/<API>?<params>`.

- Replace `<server-url>` with the region you want to search in. See the list of currently available regions below.
- Replace `<API>` with the API that you want to call. See the list of available APIs below. Note that not all APIs are currently available in all regions.
- Replace `<params>` with required and/or optional parameters. An API call can take multiple parameters. The format should be `<param1>=<value1>&<param2>=<value2>&...` See the list of parameters in each API. Note that different APIs can have different parameters.

## List of server URLs by region

- China ⇒ `https://chinalex.geolex.org`
- Indian Plate ⇒ `https://indplex.geolex.org`
- Thailand ⇒ `https://thailex.geolex.org`
- Vietnam ⇒ `https://vietlex.geolex.org`
- Niger ⇒ `https://nigerlex.geolex.org`
- Malaysia ⇒ `https://malaylex.geolex.org`
- Africa ⇒ `https://africalex.geolex.org`
- Belgium ⇒ `https://belgiumlex.geolex.org`
- Middle East ⇒ `https://mideastlex.geolex.org`
- Panama ⇒ `https://panamalex.geolex.org`
- Qatar ⇒ `https://qatarlex.geolex.org`
- South America ⇒ `https://southamerlex.geolex.org`
- Dev ⇒ `https://dev.geolex.org`

## List of APIs

### Search formations

- API ⇒ `searchAPI.php`
- Parameters
    - `<searchquery>`: String. This can be full or partial names of formations. It can also include contents in the Synonym section of formations. Optional.
    - `<periodfilter>`: String. This is the name of the period in which the search is limited. Optional.
    - `<provincefilter>`: String. This is the name of the province in which the search is limited. Optional.
    - `<agefilterstart>`: Number (Ma). The bottom age of the search. Should be non-negative if provided. Optional.
    - `<agefilterend>`: Number (Ma). The top age of the search. Should be non-negative and not greater than `<agefilterstart>` if provided. Optional. Note 1) when `<agefilterstart>` is empty, the search is not limited by time, regardless of `<agefilterend>`. 2) When `<agefilterstart>` is set and `<agefilterend>` is not, the search is limited to the time of `<agefilterstart>` (not a range).
    - `<lithofilter>`: String. This can be full or partial names of lithology patterns of formations. Multiple strings can be concatenated logically using `AND` and/or `OR` keywords. Optional.
    - `<response>`: String. The only value that will have an effect is `long`. It will ask the API to return all fields in the database. Optional.
- Return Object (each is a formation)
    - `name`: String. The name of the formation.
    - `endAge`: Number. Top age of the formation.
    - `begAge`: Number. Bottom age of the formation.
    - `province`: String. A comma separated list of the provinces which the formation is located in.
    - `geojson`: GeoJSON. Full coordinates of the formation in GeoJSON.
    - `period`: String. Bottom period of the formation.
    - `stage`: String. Bottom stage of the formation.
    - `lithology_pattern`: String. The name of the lithology pattern of the formation.
    - `isSynonym`: Boolean. If this formation is the result of a search in the Synonym section (see `<searchquery>`).
    
    Fields below are only returned when the `response=long` argument is passed in. “Optional” means that the field may be empty.
    
    - `age_interval`: String. A description of age span of the formation. Optional.
    - `lithology`: String. Full description of the lithology of the formation. Optional.
    - `lower_contact`: String. Optional.
    - `upper_contact`: String. Optional.
    - `regional_extent`: String. Optional.
    - `fossils`: String. Optional.
    - `age`: String. Optional.
    - `depositional`: String. Optional.
    - `additional_info`: String. Optional.
    - `compiler`: String. Optional.
    - `age_span`: String. Optional.
    - `beginning_stage`: String. This is the same as the `stage` field.
    - `frac_upB`: Number. The fraction of age span in the bottom stage.
    - `beg_date`: Number. This is the same as the `begAge` field.
    - `end_stage`: Number. Top stage of the formation.
    - `frac_upE`: Number. The fraction of age span in the top stage.
    - `end_date`: Number. This is the same as the `endAge` field.
    - `depositional_pattern`: String. Optional.

### Retrieve a list of provinces

- API ⇒ `provinceAPI.php`
    - This API call is currently only available in the Dev region. It will later be pushed to other regions.
- Parameters
    - No parameters needed or accepted.
- Return Object
    - An Array of province names, which are Strings, sorted by alphabetically order.

## Examples

- [https://dev.geolex.org/searchAPI.php?agefilterstart=70](https://dev.geolex.org/searchAPI.php?agefilterstart=70)
    - This searches for all formations in Dev that spans over 70 Ma regardless of their names, provinces, or lithology patterns.
- [https://dev.geolex.org/searchAPI.php?agefilterstart=70&response=long](https://dev.geolex.org/searchAPI.php?agefilterstart=70&response=long)
    - This searches for the same set of formations in Dev like the previous example. However, this query will return more detailed information for each formation.
- [https://chinalex.geolex.org/searchAPI.php?searchquery=changxing&lithofilter=limestone](https://chinalex.geolex.org/searchAPI.php?searchquery=changxing&lithofilter=limestone)
    - This searches for all formations in China that has “changxing” in their names and “limestone” in their lithology in their patterns.
- [https://dev.geolex.org/provinceAPI.php](https://dev.geolex.org/provinceAPI.php)
    - This returns an Array of province names available in Dev.