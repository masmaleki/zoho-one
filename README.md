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

## Zoho Books fulfilment (packages, shipments, receives, transfers, adjustments, batches)

These helpers target `/books/v3` on the organization's own datacenter
(`ZohoOrgCredentials::booksApiBaseUrl($internalOrganizationId)`). `$organizationId` is the **Zoho Books**
organization id; the optional trailing `$internalOrganizationId` is your application's organization id, used
for the token lookup and the datacenter (null = the ambient `zoho-one.current_internal_organization_id`).
`$condition` is an array of Zoho filters or the legacy `"&key=value"` string.

They share `ZohoBooksInventoryRequest`: success returns the decoded Zoho body; failure returns
`['code' => <Zoho code or HTTP status>, 'message' => <Zoho message>, 'http_status' => int|null,
'retry_after' => int|null (HTTP 429 Retry-After), 'zoho_one_error' => true]`. The access token never appears
in a failure. For tests, `ZohoBooksInventoryRequest::useHttpClient(fn () => $guzzleClient)` and
`ZohoBooksInventoryRequest::resolveTokenUsing(fn ($internalOrgId) => (object) ['access_token' => '…'])`
replace the transport and the token source (pass `null` to restore).

```php
// Packages
ZohoAllInOne::listPackages($organizationId, $page = 1, $condition = '', $internalOrganizationId = null);
ZohoAllInOne::getPackage($packageId, $organizationId, $internalOrganizationId = null);
ZohoAllInOne::createPackage($salesOrderId, array $payload, $organizationId, $internalOrganizationId = null); // POST /packages?salesorder_id=
ZohoAllInOne::updatePackage($packageId, array $payload, $organizationId, $internalOrganizationId = null);
ZohoAllInOne::deletePackage($packageId, $organizationId, $internalOrganizationId = null);

// Shipment orders
ZohoAllInOne::getShipmentOrders($organizationId, $page = 1, $condition = '', $internalOrganizationId = null);
ZohoAllInOne::getShipmentOrder($shipmentOrderId, $organizationId, $internalOrganizationId = null);
ZohoAllInOne::createShipmentOrder(array $packageIds, $salesOrderId, array $payload, $organizationId, $internalOrganizationId = null); // POST /shipmentorders?package_ids=a,b&salesorder_id=
ZohoAllInOne::updateShipmentOrder($shipmentOrderId, array $payload, $organizationId, $internalOrganizationId = null);
ZohoAllInOne::deleteShipmentOrder($shipmentOrderId, $organizationId, $internalOrganizationId = null);
ZohoAllInOne::markShipmentOrderDelivered($shipmentOrderId, $organizationId, $internalOrganizationId = null); // POST /shipmentorders/{id}/status/delivered

// Purchase receives
ZohoAllInOne::getPurchaseReceives($organizationId, $page = 1, $condition = '', $internalOrganizationId = null);
ZohoAllInOne::getPurchaseReceive($purchaseReceiveId, $organizationId, $internalOrganizationId = null);
ZohoAllInOne::createPurchaseReceive($purchaseOrderId, array $payload, $organizationId, $internalOrganizationId = null); // POST /purchasereceives?purchaseorder_id=
ZohoAllInOne::updatePurchaseReceive($purchaseReceiveId, array $payload, $organizationId, $internalOrganizationId = null);
ZohoAllInOne::deletePurchaseReceive($purchaseReceiveId, $organizationId, $internalOrganizationId = null);

// Transfer orders
ZohoAllInOne::getTransferOrders($organizationId, $page = 1, $condition = '', $internalOrganizationId = null);
ZohoAllInOne::getTransferOrder($transferOrderId, $organizationId, $internalOrganizationId = null);
ZohoAllInOne::createTransferOrder(array $payload, $organizationId, $internalOrganizationId = null);
ZohoAllInOne::updateTransferOrder($transferOrderId, array $payload, $organizationId, $internalOrganizationId = null);
ZohoAllInOne::deleteTransferOrder($transferOrderId, $organizationId, $internalOrganizationId = null);
ZohoAllInOne::markTransferOrderTransferred($transferOrderId, $organizationId, $internalOrganizationId = null); // UNVERIFIED: POST /transferorders/{id}/markastransferred

// Inventory adjustments
ZohoAllInOne::getInventoryAdjustments($organizationId, $page = 1, $condition = '', $internalOrganizationId = null);
ZohoAllInOne::getInventoryAdjustment($inventoryAdjustmentId, $organizationId, $internalOrganizationId = null);
ZohoAllInOne::createInventoryAdjustment(array $payload, $organizationId, $internalOrganizationId = null);
ZohoAllInOne::deleteInventoryAdjustment($inventoryAdjustmentId, $organizationId, $internalOrganizationId = null);

// Item batches — UNVERIFIED endpoint: GET /items/{id}/batches, falling back to GET /items/{id} → item.batches
// on 404 / Zoho code 5; the result carries batches_source (batches_endpoint | item_detail | item_detail_without_batches).
ZohoAllInOne::getItemBatches($itemId, $organizationId, $internalOrganizationId = null);
```

Endpoints marked UNVERIFIED come from the Zoho Inventory API reference and have not yet been confirmed on a live
Books organization.

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
