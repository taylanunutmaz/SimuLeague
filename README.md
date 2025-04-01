# SimuLeague

SimuLeague is a Laravel-based Premier League match simulator that predicts the champion while allowing users to simulate and play matches.

## Project Setup for Development

Follow these steps to set up the project for development:

```bash
cp .env.example .env

composer install

php artisan migrate --seed

npm install

npm run dev
```

## Requirements

Ensure your system meets the following requirements before setting up the project:

- PHP 8.1 or higher
- Composer
- Node.js & npm
- Database (MySQL, PostgreSQL, or SQLite)

## Running Tests

To run the test suite, use the following command:

```bash
php artisan test
```
