# SimuLeague

SimuLeague is a Laravel-based Premier League match simulator that predicts the champion while allowing users to simulate and play matches.

![image](https://github.com/user-attachments/assets/cd30cada-1f45-4bb0-a64c-15916f1ca947)

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

- PHP 8.2 or higher
- Composer
- Node.js & npm
- Database (MySQL, PostgreSQL, or SQLite)

## Running Tests

To run the test suite, use the following command:

```bash
php artisan test
```
