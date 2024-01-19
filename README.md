# Xoomify

App that can track the Spotify listening history of a group of people and generate charts.

## Local setup

### Install dependencies

```sh
composer install
npm install
```

### Run database migrations

```sh
php bin/console doctrine:migrations:migrate
```

### Development run

```sh
npm run watch
symfony server:start --no-tls
```
