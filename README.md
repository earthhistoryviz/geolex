# geolex - Geologic Lexicon Site
---------------------------------

You can clone this repository to deploy a lexicon site for a single geographic region.  To initialize,
you'll need a default_timescale.xlsx (one has been included in site/timesacles) and you will need to run `php db/createDb.php` to create the
empty database.  Then you can upload word docs to start populating your lexicon database.

You can view the existing Geologic Lexicon sites at https://geolex.org.

## Installation
---------------

```bash
git clone git@github.com:earthhistoryviz/geolex.git
```


## Operation
-------------
Requires docker.

```bash
docker-compose up -d
```
Or potentially

```bash
docker compose up -d
```
if using a newer version.

After you need to enter the container and run `php db/createDb.php`
To list running containers:
```bash
docker ps
```
To enter container:
```bash
docker exec -it container-name bash
```
Navigate to code directory once in container:
```bash
cd code
```
Run create_db.php:
```bash
php db/createDb.php
```
If running locally the url to access website is:
[Local Website]http://localhost:5100