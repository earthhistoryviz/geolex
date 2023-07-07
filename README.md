# geolex - Geologic Lexicon Site
---------------------------------

You can clone this repository to deploy a lexicon site for a single geographic region.  To initialize,
you'll need a default_timescale.xlsx and you will need to run `php db/createDb.php` to create the
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


