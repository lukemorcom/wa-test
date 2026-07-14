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
- Copy the Laravel .env: `cp laravel/.env.example laravel/.env`
- Bring up the containers: `docker compose up -d`
- Install dependencies: `rudder composer install`
- Set the application key: `rudder art key:generate`
- Migrate the database: `rudder art migrate`
- Run the tests: `rudder art test`
- Run the built in Laravel development server: `rudder art serve --host=0.0.0.0 --port=8000`
- Run the queue worker: `docker exec queue php artisan queue:work` 
- Send a csv for processing! You can import the Bruno collection mentioned below, or just run this cURL from `/bruno/Investments`:

``` bash
curl -X POST http://localhost:8000/api/investments/import \
-F "file=@sample-investments.csv" \
-H "Accept: application/json"
```

- Observe the queue worker CLI outputting the info
- Use a DB client or just use Docker to peek in the MySQL container and look at the `laravel` database:

``` SQL
mysql> use laravel

Database changed
mysql> select * from investors;
+----+-------------+------------------+-----+---------------------+---------------------+
| id | investor_id | name             | age | created_at          | updated_at          |
+----+-------------+------------------+-----+---------------------+---------------------+
|  1 | INV001      | Terry Tibbs      |  30 | 2026-07-14 20:51:01 | 2026-07-14 20:51:01 |
|  2 | INV002      | George Agdgdwngo |  25 | 2026-07-14 20:51:01 | 2026-07-14 20:51:01 |
+----+-------------+------------------+-----+---------------------+---------------------+
2 rows in set (0.00 sec)

mysql> select * from investments;
+----+-------------+---------+-----------------+---------------------+---------------------+
| id | investor_id | amount  | investment_date | created_at          | updated_at          |
+----+-------------+---------+-----------------+---------------------+---------------------+
|  1 |           1 | 1000.50 | 2026-07-12      | 2026-07-14 20:51:01 | 2026-07-14 20:51:01 |
|  2 |           2 | 2500.00 | 2026-07-12      | 2026-07-14 20:51:01 | 2026-07-14 20:51:01 |
|  3 |           1 | 1500.75 | 2026-07-13      | 2026-07-14 20:51:01 | 2026-07-14 20:51:01 |
+----+-------------+---------+-----------------+---------------------+---------------------+
3 rows in set (0.00 sec)
```


## API Documentation

This project utilises Bruno for API docs.

The collection is saved in the `/bruno` directory. It contains:
- Configured environment for `Local` (`http://localhost:8000`)
- Automated assertions
- Testing assets