# Geolex Website

## Code Structure

### Front End
* `index.php` is the home page and the entry point of the website. This page handles any interaction with the local database. For example, if a user is visiting the Chinalex website, then `index.php` will only show formations from the China region.
* `general.php` is the Multi-Country Search page and provides connection to all of the websites. This page is able to query results from any databases available.
* `generalSearchBar.php` defines the search functionality of both pages. Depending on which page it is being used in, it may show/hide region or province selection.
* `displayInfo.php` defines the formation detail page. When users click on any search result, they will be redirected here.

### Back End
* `SqlConnection.php` constructs the connection between the website and the database.
* `generateRecon.php` calls Python scripts in `pygplates/` to generate reconstruction images.
* `searchFmAddDelete.php` controls admin actions on formation records in the database.

### API
See [API_Guide.md](./API_Guide.md).

## TODO
### Code Refactor
* Deprecate `searchFm.php` and `searchBar.php`, and use `generalSearchBar.php` for all search bars, including the one in Admin Dashboard.
* 

### New Features
* Make reconstruction image interactive. See forum question https://forum.generic-mapping-tools.org/t/methods-to-extract-pixel-coordinates-after-plotting/3987.
* 
