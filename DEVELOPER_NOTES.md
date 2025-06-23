# Developer Notes

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Technology Stack](#technology-stack)
3. [Installation & Setup](#installation--setup)
    1. [Dependencies](#dependencies)
    2. [Environment Configuration](#environment-configuration)
    3. [Database Setup](#database-setup)
    4. [Frontend Build](#frontend-build)
    5. [Web Server Setup](#web-server-setup)
4. [Local Development](#local-development)
    1. [Docker Setup](#docker-setup)
    2. [Database Migrations](#database-migrations)
5. [Testing](#testing)
    1. [Test Structure](#test-structure)
    2. [Test Database](#test-database)
    3. [Running Tests](#running-tests)
    4. [Test Coverage](#test-coverage)
6. [Authentication](#authentication)
    1. [SAML2 Identity Provider Setup](#saml2-identity-provider-setup)
    2. [Provider Examples](#provider-examples)
7. [Development Guidelines](#development-guidelines)
    1. [PHP Documentation](#php-documentation)
    2. [GitHub Workflows](#github-workflows)
        1. [Pull Request Checks](#pull-request-checks)
        2. [Code Style](#code-style)
8. [System Enhancements](#system-enhancements)
    1. [SLSKey Group Configuration](#slskey-group-configuration)
    2. [Custom Webhook Integration](#custom-webhook-integration)
9. [Reporting](#reporting)
    1. [Custom Activation Reports](#custom-activation-reports)

## Prerequisites

- PHP >= 8.2.20
- Composer
- NodeJS & NPM
- MySQL Database
- Apache or Nginx
- Redis server (required for queue functionality)
- Pusher account (for queue monitoring)
- Supervisor (optional, recommended for queue management)

## Technology Stack

- Laravel
- Inertia JS
- VueJS Frontend
- Tailwind CSS
- Laravel Sail (Docker)
- MySQL database
- Pest PHP Testing Framework

## Installation & Setup

### Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev
php artisan optimize

# Queue setup (required)
sudo apt install redis-server
systemctl start redis-server
```

### Queue Worker Setup

There are two ways to run the queue worker:

#### Option 1: Manual Queue Worker (Simple)

Run this command to start processing jobs:
```bash
php artisan queue:work --queue=redis_import_job --timeout=12000  # 200 minutes timeout
```
Note: This command needs to be restarted manually if the server restarts or the process ends.

#### Option 2: Supervisor Setup (Recommended)

Supervisor keeps the queue worker running permanently and restarts it if it fails:

1. Install Supervisor:
```bash
sudo apt-get install supervisor
```

2. Create Supervisor configuration:
```bash
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

Add this configuration:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /data/webroot/slskey-backend/artisan queue:work --sleep=3 --queue=redis_import_job
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=webadm
numprocs=8
redirect_stderr=true
stdout_logfile=/data/webroot/slskey-backend/storage/logs/worker.log
stopwaitsecs=90000
```

3. Start Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start "laravel-worker:*"
```

### Environment Configuration

1. Copy `.env.example` to `.env`
2. Update configuration values marked with `<>`
3. Configure queue-related settings:
   - PUSHER_APP_ID
   - PUSHER_APP_KEY
   - PUSHER_APP_SECRET
   - PUSHER_APP_CLUSTER

### Database Setup

```bash
# Create and migrate database
php artisan migrate

# Seed initial data
php artisan db:seed
```

### Frontend Build

```bash
# Production build
npm run prod
```

### Web Server Setup

#### Apache Configuration

Create a virtual host configuration file (e.g., `/etc/apache2/sites-available/slskey.conf`):

```apache
<VirtualHost *:443>
    ServerAdmin info@slsp.ch
    ServerName slskey2-test.swisscovery.network
    DocumentRoot /data/webroot/slskey-backend/public

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined

    SSLEngine on
    SSLCertificateFile      /etc/ssl/certs/slskey-test.pem
    SSLCertificateKeyFile   /etc/ssl/private/slskey-test.key

    <FilesMatch "\.(?:cgi|shtml|phtml|php)$">
        SSLOptions +StdEnvVars
    </FilesMatch>

    <Directory "/data/webroot/slskey-backend/public">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Enable the required Apache modules:
```bash
sudo a2enmod rewrite
sudo a2enmod ssl
```

For improved performance, you can optionally use PHP-FPM instead of the Apache PHP module. This provides better resource management and faster processing of PHP requests.

## Local Development

It's recommended to use Docker for local development to ensure consistency across environments.

### Docker Setup

1. Configure environment:
   - Create `.env` file
   - Set `APP_ENV=dev`
   - Set `DB_HOST=mysql`
   - Update `DB_USERNAME` (avoid using 'root')

2. Initialize development environment:
```bash
composer install --ignore-platform-reqs --dev
./vendor/bin/sail up # start Docker containers
./vendor/bin/sail npm install # install Node dependencies
./vendor/bin/sail npm run watch # build frontend & start frontend watcher
```

The application will be available at `localhost:80`

### Database Migrations

```bash
# Reset and migrate database
./vendor/bin/sail artisan migrate:refresh

# Seed development data
./vendor/bin/sail artisan db:seed
```

## Testing

### Test Structure

- **Feature Tests**: `tests/Feature/` - Tests application endpoints
- **Integration Tests**: `tests/Integration/` - Tests business logic and workflows

Note: Unit tests are not included as individual functions are not complex enough to warrant them.

### Test Database

- Uses in-memory SQLite
- Temporary database exists only during test execution
- Test data populated from `database/seeders/Test`

### Running Tests

```bash
# Run tests using docker
./vendor/bin/sail artisan test

# If tests fail, try clearing config:
./vendor/bin/sail artisan config:clear
```

### Test Coverage

```bash
# Generate coverage report (requires XDebug)
./vendor/bin/sail artisan test --coverage
```

## Authentication

### SAML2 Identity Provider Setup

This application uses [24slides/laravel-saml2](https://github.com/24Slides/laravel-saml2) for SAML2 authentication.

Management commands:
```bash
# List current providers
php artisan saml2:list-tenants

# Remove provider
php artisan saml2:delete-tenant <key-of-existing>

# Add new provider
php artisan saml2:create-tenant --key=<idp-key> --entityId=<entity-id> --loginUrl=<login-url> --logoutUrl=<logout-url> --x509cert="<cert>"
```

Note: The SP X509 Certificate should be configured in `.env`:
- SAML2_SP_CERT_x509
- SAML2_SP_CERT_PRIVATEKEY

### Provider Examples

<details>
<summary>Switch edu-ID Configuration</summary>

```bash
php artisan saml2:create-tenant --key=eduid --entityId=https://eduid.ch/idp/shibboleth --loginUrl=https://login.eduid.ch/idp/profile/SAML2/Redirect/SSO --logoutUrl=https://login.eduid.ch/idp/profile/SAML2/Redirect/SLO --x509cert="MIID6jCCAlKgAwIBAgIUQYBVlHR1BTuCRvxNID/1YViWvXswDQYJKoZIhvcNAQELBQAwEzERMA8GA1UEAxMIZWR1aWQuY2gwHhcNMjMxMTAxMTAwMDA0WhcNMzMxMjE4MTAwMDA0WjATMREwDwYDVQQDEwhlZHVpZC5jaDCCAaIwDQYJKoZIhvcNAQEBBQADggGPADCCAYoCggGBAJ7otyXaPNpqa2T4sIw3D+G9qdns5fGWYPyKkqkbSwM/xcWY86JTyG4bNaI9za1mVmX/+JZ3rNjmihHh1pNc8Y4U3sroX4YmpHme7U0o5QzgpXgtg23NyWGSTKJG4Z4LhoPXvBvcwhTE2wRbcIiiHQExXrmntq6QXafum2eSy0wuQcsdy+jiJX1shyDC29Epf1ObglfbUYS1GkfBaV9QfsKDbEBWixdALTpubcxmEHONdmNn8wrr2IPkVod+pBgLGaacRHKt/O7aw3R5FlAOw8KANJMU9MDrp7yhH3HzQPMyc5E8HNIbh284NY3etkj0rDtI8Py2DRjMkZeUdkqyCmrskwd0aUXKTOjBT0u3TD4hayuIUUx7w3Fb3MaEWY16mFVrlw5/6AsRIiFQGbxXwXV41oTxiSBGAYBoux27Db5j6H3XEseKamk5oH8z/4HqhtzCA5oMCYkwWpf2NXwJks6T378bSw0VSklQXcoBcwPjByyrZVHpV+8bOzJdbEfRHQIDAQABozYwNDATBgNVHREEDDAKgghlZHVpZC5jaDAdBgNVHQ4EFgQUUXM4hK0yc1TiLRbbgkfB6FZ6jyowDQYJKoZIhvcNAQELBQADggGBAJN1C/JIa4hfpb7HwdSGQ/fnxRV+m/V1wp5LEZKEd/P1eIjb3ETkUL4wP90a+B22ndJIF46FqmRwu1KtVufCQE1ptYJ7JqmQEg5v1rHKMzV8CYEIZm9Wlb/dto15TAs/CkQSHyzpaeg4cNpruGtiIPUB1q01UM8NEWWU9vgrvwJjj3j3/mJPN6FWtPtZDCnIojEDV3QaeXzxQhPn5UsJuCjWX2Y81AQdVjSkbKAxyy9BWIRV2Ib6FWBZzVQwbjFADZKTiIortzAVYCrJHHU/c5enI19U6Y8/+xzu97w3STiQLgvlUUhNNgmaYMKYtRWSrqW1aaSIQ4tiylNLEb0QdoG03mDZ60WUsRF87ZrlTYU+vMNj3d4dsF70mLK+0IEyKud2r/76LQg8AEF6ESX8i3n6IGG3Tt1zeBbVZ4zNtFjvzmef7CYyLsu5MP1QBGI4QLV19rBn+6JszkavM972cTuZTVZ1BU2/g/4ZSq85+XoKRmA2GH+Wega5gX63BIOnAQ=="
```
</details>

<details>
<summary>SLSP AuthProxy Configuration</summary>

```bash
php artisan saml2:create-tenant --key=authproxy --entityId=https://authproxy.swisscovery.network/realms/slskey-unibas --loginUrl=https://authproxy.swisscovery.network/realms/slskey-unibas/protocol/saml --logoutUrl=https://authproxy.swisscovery.network/realms/slskey-unibas/protocol/saml --x509cert="MIICqTCCAZECBgGLkHsVfzANBgkqhkiG9w0BAQsFADAYMRYwFAYDVQQDDA1zbHNrZXktdW5pYmFzMB4XDTIzMTEwMjE0MzkyN1oXDTMzMTEwMjE0NDEwN1owGDEWMBQGA1UEAwwNc2xza2V5LXVuaWJhczCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBALhN10HWC7PUCMM+uE+D2rQAPt0dO9C1k5lbYMKF2UNuTz6s81xy2FbYT/grSBt/FknPVbzmKMUMIEZl7T1f5OQ466eTd6yLXss2CsIIGTjBx4/qbJyZkgAxhiEf0HTx0asoeLJ+wgx8dTwE3cT1xFuciQKqYg8XkMSPFIxLhB8cI9DCz3znSvfS5NmeEnhDYvp/sdWWhJNbiZQn+RAethFCV6cHcCsjiZ9Xan0MIw0PZhrxNJpzvtRI1bgjAwvTVhUp5S//zsLIrHo3pSdWaEECkkiPAHsmc9izvFu3rcTbB/9jbexR8mWa2zIOa/JJHi/DB+wjYt1L54V+i5me0ZMCAwEAATANBgkqhkiG9w0BAQsFAAOCAQEARIBiet0/ntNwf1Bsl2ilpfHEhVcFHUNh+9gCBlt1GRVCf3DPE7lhEz83jApxqSDW8Xo81GGObOkvGgBN8yGOZk13hIRqRIhWGMQVilE5UsI5wXWPZ8xOuzXWyYW2kmCoYsHX+G4ziJ11zWy9F99G9kbxiFQE54lmPYSXWxTbHYjOd+PxW9LaastJ90yKuf9ZDELBd5TYg6gjTg58ilXQN7FrKLxiq6dJSfHzfP+Kjrb5mD+Sp0loq3a46niccKdedf19zUU2qiMIXP4cf6LOlVuqY5fbBHsuLHSqXK0g01MKdUuW/NZ9cPLVlLOFq1Mu/UCiCO4HBlGy4NAtTDiiAg=="
```
</details>

## Development Guidelines

### PHP Documentation

Use VS Code Plugin `PHP DocBlocker` to generate PHP Doc Headers.

### GitHub Workflows

#### Pull Request Checks

All PRs are automatically checked for:
1. Code style compliance
2. Test passing status
3. Test coverage requirements
4. Security checks (npm audit, composer audit)

#### Code Style

```bash
# Check code style
./vendor/bin/pint --test

# Fix code style issues
./vendor/bin/pint
```

## System Enhancements

### SLSKey Group Configuration

When adding new SLSKey Groups:
1. Add group in SLSKey Admin Panel
2. Add API Key to `services.php` config
3. Add corresponding environment variable to `.env`

### Custom Webhook Integration

To create custom webhook verifiers:
1. Create new class in `app/Helpers/CustomWebhookVerifier/Implementations`
2. Implement `CustomWebhookVerifierInterface`
3. Implement `verify` method returning boolean
4. Verifier will be automatically available in UI

## Reporting

### Custom Activation Reports

For example, AKB (Aargauer Kantonsbibliothek) requested a report about activations.
Example query for activation history:
```sql
SELECT 
    slskey_groups.slskey_code,
    slskey_users.primary_id,
    slskey_histories.created_at,
    slskey_histories.action,
    slskey_histories.trigger,
    slskey_histories.author
FROM slskey_histories
INNER JOIN slskey_users
    ON slskey_histories.slskey_user_id = slskey_users.id
INNER JOIN slskey_groups
    ON slskey_histories.slskey_group_id = slskey_groups.id
WHERE 
    (slskey_groups.id IN (544, 545))
    AND slskey_histories.action NOT IN (
        'REMINDED',
        'TOKEN_SENT',
        'NOTIFIED',
        'EXPIRATION_DISABLED'
    );
