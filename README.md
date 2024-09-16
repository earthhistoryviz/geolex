# Geolex - Geologic Lexicon Site

Geolex allows you to deploy a lexicon site for a single geographic region. This repository contains everything you need to get started, including a default timescale file and a script to create an empty database. You can populate the lexicon database by uploading Word documents.

Visit existing Geologic Lexicon sites at [https://geolex.org](https://geolex.org).

## Installation

Clone the repository to get started:

```bash
git clone git@github.com:earthhistoryviz/geolex.git
```

## Operation

Geolex requires Docker for operation. Use the following commands based on your Docker version:

### Using Docker

```bash
docker-compose up -d
```

### Using Docker Compose (Newer Version)

```bash
docker compose up -d
```

### Accessing the Site Locally
The local URL for the site will be: [http://localhost:5100](http://localhost:5100)

# Prettier command
```bash
docker run -it --rm -v $(pwd):/code ghcr.io/php-cs-fixer/php-cs-fixer:3.57-php8.0 fix site
```
