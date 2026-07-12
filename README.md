# Csv Service

[![Laravel CI](https://github.com/lukemorcom/wa-test/actions/workflows/laravel.yml/badge.svg)](https://github.com/lukemorcom/wa-test/actions/workflows/laravel.yml)


## Local architecture

Uses Docker Compose to provide lightweight local environment.

- `app` - php container, entry point is `artisan serve` which is just for local
- `queue` - shares an image (and locally, a volume mapping) with `app`. Executes async jobs
- `redis` - cache and stores queued jobs


## Local setup

- Clone the repository and cd to the root
- 'Install' the rudder helper: `cp rudder /usr/local/bin/rudder`
- Copy the .env: `cp .env.example .env`
- Bring up the containers: `docker compose up -d`
- Migrate the database: `rudder art migrate`
- Run the tests: `rudder art test`
