# Csv Service

[![Laravel CI](https://github.com/lukemorcom/wa-test/actions/workflows/laravel.yml/badge.svg)](https://github.com/lukemorcom/wa-test/actions/workflows/laravel.yml)


## Local architecture

Uses Docker Compose to provide lightweight local environment.

- `app` - php container, entry point is `artisan serve` which is just for local
- `queue` - shares an image (and locally, a volume mapping) with `app`. Executes async jobs
- `redis` - application cache, stores queued jobs


## Local setup

- Clone the repository and cd to the root
- 'Install' the rudder helper: `cp rudder /usr/local/bin/rudder`
- Copy the .env: `cp .env.example .env`
- Bring up the containers: `docker compose up -d`
- Install dependencies: `rudder composer install`
- Migrate the database: `rudder art migrate`
- Run the tests: `rudder art test`
- Send a csv for processing! You can import the Bruno collection mentioned below, or just run this cURL from `/bruno/Investments`:

``` bash
curl -X POST http://localhost:8000/api/investments/import \
-F "file=@sample-investments.csv" \
-H "Accept: application/json"
```


## API Documentation

This project utilises Bruno for API docs.

The collection is saved in the `/bruno` directory. It contains:
- Configured environment for `Local` (`http://localhost:8000`)
- Automated assertions
- Testing assets