# Query From Cache

Query From Cache is a Laravel package that provides a simple way to cache query results directly from your Eloquent models. It automatically caches the output of model methods and offers actions to refresh or clear the cache with minimal code changes.

## Features

- **Automatic Caching**: Call a method with the suffix `FromCache` and have its result cached.
- **Custom Cache Actions**: Use actions like **CREATE**, **REFRESH**, and **FORGET** to control cache behavior.
- **Configurable**: Easily adjust cache prefix, expiration time, and cache store via configuration.
- **Easy Integration**: Use a trait on your models to enable caching without modifying your existing methods.

## Installation

You can install the package via Composer:

```bash
composer require caiquemcz/query-from-cache
```

Then, register the service provider in your `config/app.php` if you are not using package auto-discovery:

```php
'providers' => [
    // ...
    CaiqueMcz\QueryFromCache\QueryFromCacheServiceProvider::class,
],
```

Publish the configuration file to customize cache settings:

```bash
php artisan vendor:publish --tag=config
```

This will create a file named `query-from-cache.php` in your `config` folder.

## Configuration

The published config file (`config/query-from-cache.php`) looks like this:

```php
<?php

use Illuminate\Support\Facades\Cache;

return [
    // Optional prefix to add to your cache keys
    'prefix' => null,

    // Default cache time in minutes
    'time_minutes' => 60,

    // Cache store class (by default, using Laravel's Cache facade)
    'class' => Cache::class,
];
```

## Usage

### Enabling Query Caching on a Model

To use caching on a model, simply include the `HasQueryFromCache` trait:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use CaiqueMcz\QueryFromCache\Traits\HasQueryFromCache;

class User extends Model
{
    use HasQueryFromCache;

    // Your model code…

    public function find()
    {
        // Your query logic, for example:
        return $this->where('active', 1)->first();
    }
}
```

### Calling Cached Methods

After including the trait, you can call a cached version of any method. The trait intercepts calls to methods with the suffix `FromCache`:

```php
use  \CaiqueMcz\QueryFromCache\Enums\CacheAction;
// Basic usage: caches the result of the "find" method
$user = User::findFromCache();
// Basic usage: caches the result of the "find" method for 30 minutes.
$user = User::findFromCache(30);
// Refresh the cache: clears and re-caches the value.
$user = User::findFromCache(30, CacheAction::REFRESH());

// Forget the cache: clears the cache entry.
$result = User::findFromCache(30, CacheAction::FORGET());
```

> **Note:**  
> The first parameter is the cache expiration time in minutes.  
> The second optional parameter allows you to specify a cache action (CREATE by default).

### How It Works

When you call `findFromCache`, the trait:
1. Validates the method name (e.g., `findFromCache` becomes `find`).
2. Checks if a cache entry exists (using a key generated as: `{prefix}{method}:{table}:{model_key}`).
3. If the cache exists and no refresh is requested, returns the cached result.
4. If not, calls the original method (`find()`), caches its result, and then returns it.
5. If the `FORGET` action is specified, it clears the cache entry.

## Testing

The package comes with a full set of unit and integration tests using PHPUnit, Mockery, and Orchestra Testbench. To run the tests:

```bash
composer test
```

Or directly via PHPUnit:

```bash
vendor/bin/phpunit
```

For more details on testing, see the `tests/` folder.

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository.
2. Create a feature branch.
3. Write tests for your changes.
4. Open a pull request describing your changes.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).