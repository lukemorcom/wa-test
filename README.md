# Csv Service

## Local architecture

Uses Docker Compose to provide lightweight local environment.


## Local setup

- Clone the repository and cd to the root
- 'Install' the rudder helper: `sudo cp rudder /usr/local/bin/rudder`
- Bring up the containers: `docker compose up -d`
- Migrate the database: `rudder art migrate`
