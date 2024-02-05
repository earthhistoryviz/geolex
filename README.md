# Geolex - Geologic Lexicon Site

Geolex allows you to deploy a lexicon site for a single geographic region. This repository contains everything you need to get started, including a default timescale file and a script to create an empty database. You can populate the lexicon database by uploading Word documents.

Visit existing Geologic Lexicon sites at [https://geolex.org](https://geolex.org).

## Installation

Clone the repository to get started:

\```bash
git clone git@github.com:earthhistoryviz/geolex.git
\```

## Operation

Geolex requires Docker for operation. Use the following commands based on your Docker version:

### Using Docker

\```bash
docker-compose up -d
\```

### Using Docker Compose (Newer Version)

\```bash
docker compose up -d
\```

### Post-Deployment Steps

After deployment, you'll need to enter the Docker container and run a script to create the database:

1. **List Running Containers**

   \```bash
   docker ps
   \```

2. **Enter the Container**

   \```bash
   docker exec -it container-name bash
   \```

3. **Navigate to the Code Directory**

   \```bash
   cd code
   \```

4. **Run the Database Creation Script**

   \```bash
   php db/createDb.php
   \```

5. **Accessing the Site Locally**

   The local URL for the site will be: [http://localhost:5100](http://localhost:5100)
