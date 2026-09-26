# Alumni Management Platform

An alumni management platform built with Laravel. The application supports alumni registration and profiles, graduate records, announcements, surveys, and separate admin workflows.

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js and npm
- SQLite, MySQL, or another database supported by Laravel

## Getting Started

Clone the repository and install the application dependencies:

```bash
git clone <repository-url>
cd project-two
composer run setup
```

The setup script installs PHP and JavaScript dependencies, creates the environment file, generates the application key, runs migrations, and builds frontend assets.

Start the local development environment with:

```bash
composer run dev
```

The application will be available at the URL shown by Laravel's development server.

## Available Commands

```bash
# Build frontend assets
npm run build

# Run the test suite
composer test

# Run Laravel's development server and frontend tooling
composer run dev
```

## Application Areas

- Public home, registration, and login pages
- Authenticated alumni dashboard and profile management
- Alumni surveys and survey responses
- Admin announcements and survey management
- Graduate records and graduate import workflows

## Collaboration

1. Create a focused branch from the current main branch:

	```bash
	git checkout main
	git pull origin main
	git checkout -b feature/short-description
	```

2. Keep changes focused and follow the existing Laravel conventions. Update migrations, tests, and documentation when a change requires them.

3. Before opening a pull request, run:

	```bash
	composer test
	npm run build
	```

4. Use clear commit messages that describe the change, such as `Add alumni survey export`.

5. Open a pull request with a short summary, testing details, screenshots for UI changes, and any migration or configuration notes. Keep the branch up to date with `main` and address review feedback before merging.

Do not commit `.env`, credentials, generated secrets, or other environment-specific files.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Create admin
php artisan alumni:make-admin admin@example.edu