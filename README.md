# Zoho all in one for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/masmaleki/zoho-one.svg?style=flat-square)](https://packagist.org/packages/masmaleki/zoho-one)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/masmaleki/zoho-one/run-tests.yml?branch=main&label=tests)](https://github.com/masmaleki/zoho-one/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/masmaleki/zoho-one.svg?style=flat-square)](https://packagist.org/packages/masmaleki/zoho-one)

Laravel package for integration with the Zoho v3 API (CRM + Books + Inventory) with multi-organization token support.

## Installation

Install the package via Composer:

```bash
composer require masmaleki/zoho-one
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="zoho-one-migrations"
php artisan migrate
```

Publish the config file:

```bash
php artisan vendor:publish --tag="zoho-one-config"
```

The config is published to `config/zoho-one.php`. Reference values in your app via `config('zoho-one.*')`.

## Upgrading from `masmaleki/zoho-v3` or `masmaleki/zoho-v4`

This package was previously published as `masmaleki/zoho-v3` and briefly as `masmaleki/zoho-v4`.
The package name no longer carries a version suffix; the underlying database table is still
named `zoho_v4` (it is an internal implementation detail and is not renamed again to avoid
forcing another migration on existing installs).

To upgrade:

1. Swap the Composer requirement:

    ```bash
    composer remove masmaleki/zoho-v3   # or masmaleki/zoho-v4
    composer require masmaleki/zoho-one
    ```

2. Publish and run the migrations. Two guarded upgrade migrations ship with the package
   (both are idempotent and safe to re-run):

    ```bash
    php artisan vendor:publish --tag="zoho-one-migrations"
    php artisan migrate
    ```

   - `rename_zoho_v3_to_zoho_v4_table` renames the legacy `zoho_v3` table to `zoho_v4`
     (no-op if you are already on `zoho_v4` or on a fresh install).
   - `add_organization_id_to_zoho_v4_table` adds the `organization_id` column required by
     the multi-organization feature (no-op if already present).

3. Re-publish the config (it has been renamed to `config/zoho-one.php`) and copy any
   custom values from your old `config/zoho-v3.php` or `config/zoho-v4.php`:

    ```bash
    php artisan vendor:publish --tag="zoho-one-config"
    ```

   Update any `config('zoho-v3.*')` or `config('zoho-v4.*')` calls in your application
   code to `config('zoho-one.*')`.

## Compatibility

| Component | Range |
|---|---|
| PHP | 8.2 / 8.3 / 8.4 |
| Laravel | 10.x / 11.x / 12.x / 13.x |

## Zoho Books customization resources

The package includes helpers for Zoho Books customization resources. These methods use the configured
Books API base URL from `config('zoho-one.books_api_base_url')`, the saved Zoho access token, and the
Zoho Books organization ID passed to the method.

```php
use Masmaleki\ZohoAllInOne\ZohoAllInOne;

$organizationId = 123456789;

$customField = ZohoAllInOne::createBooksCustomField([
    'label' => 'Internal Number',
    'data_type' => 'string',
], $organizationId);

$webhook = ZohoAllInOne::createBooksWebhook([
    'name' => 'Sales order sync',
    'url' => 'https://example.com/api/zoho/books/webhook',
], $organizationId);

$customFunction = ZohoAllInOne::createBooksCustomFunction([
    'name' => 'Sync sales order',
    'script' => '// Deluge script body',
], $organizationId);

$customAction = ZohoAllInOne::createBooksCustomAction([
    'name' => 'Run sync',
], $organizationId);
```

Available helper groups:

```php
ZohoAllInOne::getBooksCustomFields($organizationId, $query = []);
ZohoAllInOne::createBooksCustomField($data, $organizationId);
ZohoAllInOne::getBooksCustomField($fieldId, $organizationId);
ZohoAllInOne::updateBooksCustomField($fieldId, $data, $organizationId);
ZohoAllInOne::deleteBooksCustomField($fieldId, $organizationId);

ZohoAllInOne::getBooksWebhooks($organizationId, $query = []);
ZohoAllInOne::createBooksWebhook($data, $organizationId);
ZohoAllInOne::getBooksWebhook($webhookId, $organizationId);
ZohoAllInOne::updateBooksWebhook($webhookId, $data, $organizationId);
ZohoAllInOne::deleteBooksWebhook($webhookId, $organizationId);

ZohoAllInOne::getBooksCustomFunctions($organizationId, $query = []);
ZohoAllInOne::createBooksCustomFunction($data, $organizationId);
ZohoAllInOne::getBooksCustomFunction($functionId, $organizationId);
ZohoAllInOne::updateBooksCustomFunction($functionId, $data, $organizationId);
ZohoAllInOne::deleteBooksCustomFunction($functionId, $organizationId);

ZohoAllInOne::getBooksCustomActions($organizationId, $query = []);
ZohoAllInOne::createBooksCustomAction($data, $organizationId);
ZohoAllInOne::getBooksCustomAction($actionId, $organizationId);
ZohoAllInOne::updateBooksCustomAction($actionId, $data, $organizationId);
ZohoAllInOne::deleteBooksCustomAction($actionId, $organizationId);
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Mohammad Sadegh Maleki](https://github.com/masmaleki)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
