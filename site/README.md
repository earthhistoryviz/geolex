# Geolex Website

## High-Level Overview

### The lexicons are:
* Africalex
* Belgiumlex
* Chinalex
* Indplex
* Japanlex
* KazakhstanLex
* Malaylex
* Mideastlex
* Nigerlex
* NigeriaLex
* MozambiqueLex
* Panamalex
* Qatarlex
* Southamerlex
* Thailes
* Vietlex

### In addition there is:
* geolex - The main webpage
* dev - The development site
* treatise - An experimental site for fossils
* proxy - A Docker container running nginx, handling network traffic to the geolex domain

Each lexicon/site has a folder in `/home/aaron/live` which contains the code for each website.

### Docker/Docker Compose
Every single lexicon lives inside a Docker container and Docker Compose is used to manage all the containers. The containers volume mount all the folders from the server into the `/app` directory inside each container. They also volume mount the MySql database for each container. To modify the container make any changes to the Dockerfile in the dev folder and build only the dev one. You can do this by running the command `docker-compose build dev` inside the folder `/home/aaron/live` (make sure that build is commented out in the `docker-compose.yml` file). If the build is succesful you can update all the containers by running `docker-compose up`. When modifying any library in the Dockerfile, it is important you double check library compatability.

### Adding a new site
You can clone the dev repository `git clone file:///home/aaron/git-repos/geolex` to deploy a lexicon site for a single geographic region.  To initialize,
you'll need a default_timescale.xlsx and you will need to run `php db/createDb.php` to create the
empty database.  Then you can upload word docs to start populating your lexicon database.

You can view the existing Geologic Lexicon sites at https://geolex.org.

Also need to manually add in the aboutPage.php because some sites have special aboutPage.php
Any updates to these aboutPage.php need to be manually added to all other sites.

#### Installation
---------------

```bash
git clone git@github.com:earthhistoryviz/geolex.git
```

#### Operation
-------------
Requires docker.

```bash
docker-compose up -d
```
There are a couple of more steps if you want this site to be accessible on the internet (for our server):
1. You need to add a new server block to the nginx configuration files located in the `/home/aaron/live/proxy`. Just copy the format of the previous blocks exactly and just change things specific to the site, like server name or proxy. If the domain is geolex this is all you have to do for this step. If not you need to talk to Aaron about this.
2. To the `docker-compose.yml` file in `/home/aaron/live` add a new block for the new container. Just copy the format, decide what should be volume mounted, and change the name.
3. Start the container using the up command from before.
4. Restart the proxy container using `docker-compose restart proxy`

Docker compose sets up a docker network so that all the containers can talk to each other. The proxy container listens to incoming traffic and based on the header (dev.geolex.org), decides on how to redirect the traffic. It uses the docker network to redirect traffic to the specific container.

### Updating code
All development should happen on the dev site. Once you're sure the code works, use `git add` and `git commit` in the dev folder. After use `sudo git push` to push the code. Outside the dev folder there is a script called [do_release](./do_release) that will go into each directory and do a `git pull`.

### MySQL and Apache/URL Redirects

#### MySQL
Each lexicon runs an instance of MySQL and Apache in the container. If you wanted to delete all formations from a database, currently the only way is to execute commands inside MySQL inside a container. You would do this by running `docker exec -it <image-name> bash` and then in the container run `mysql`. After that you can use SQL to alter the database. Do this only after running the [backup_dbs](./backup_dbs) to backup the databases.

#### Apache/URL Redirects
URL redirects are used in each lexicon to simplify URLs as well as improve SEO. There are currently two places where this happens. Once doing a search using the search bar on the main page or the multi-country page, each formation is linked to with a URL like this: `example.geolex.org/formations/formation-name`. This url is rewritten to `example.geolex.org/displayInfo.php?formation=formation-name`. The other URL rewritten is `example.geolex.org/full-search` located in the footer of each site. This redirects to `example.geolex.org/fullSearch.php`. All these redirects can be found in the `.htaccess` file located in each sites' folder.

For both Apache and MySQL, the logs are inside each container in `/var/log/mysql` or `/var/log/apache2`

## Code Structure

### Front End
* `index.php` is the home page and the entry point of the website. This page handles any interaction with the local database. For example, if a user is visiting the Chinalex website, then `index.php` will only show formations from the China region.
* `general.php` is the Multi-Country Search page and provides connection to all of the websites. This page is able to query results from any databases available.
* `generalSearchBar.php` defines the search functionality of both pages. Depending on which page it is being used in, it may show/hide region or province selection.
* `displayInfo.php` defines the formation detail page. When users click on any search result, they will be redirected here.
* `navBar.php` show the navigation bar at the top and show `adminDash.php` if logged in

### Back End
* `SqlConnection.php` constructs the connection between the website and the database.
* `generateRecon.php` calls Python scripts in `pygplates/` to generate reconstruction images.
* `makeImageMap.php` similar to `generateRecon.php` except also adds HTML maps to add interactivity to the images.

### API
See [API_Guide.md](./API_Guide.md).

## TODO
[x] - Complete
[ ] - Incomplete

### Code Refactor
- [x] Deprecate `searchFm.php` and `searchBar.php`, and use `generalSearchBar.php` for all search bars, including the one in Admin Dashboard.
- [X] Deprecate `generateRecon.php` and use `makeImageMap.php`, which is responsible for overlaying the html map on the reconstruction images. Currently the code in `generateRecon.php` is just a copy of `makeImageMap.php`.

### New Features
* [x] Make reconstruction image interactive. See forum question https://forum.generic-mapping-tools.org/t/methods-to-extract-pixel-coordinates-after-plotting/3987.
* [ ] Find other ways to improve SEO (make site more mobile friendly, clean URLs).
* [ ] Add AI to summarize despositional environment.