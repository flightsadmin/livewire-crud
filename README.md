# Livewire Crud Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/flightsadmin/livewire-crud.svg?style=flat-square)](https://packagist.org/packages/flightsadmin/livewire-crud)
[![Build Status](https://img.shields.io/travis/flightsadmin/livewire-crud/master.svg?style=flat-square)](https://travis-ci.org/flightsadmin/livewire-crud)
[![Total Downloads](https://img.shields.io/packagist/dt/flightsadmin/livewire-crud.svg?style=flat-square)](https://packagist.org/packages/flightsadmin/livewire-crud)

A livewire CRUD Generation package to help scaffold basic site files. Package is autoloaded as per PSR-4 autoloading in any laravel version `^5.6` so no extra config required. However is has been tested on version `^7 & ^8`. It uses ***auth*** middleware thus installs `laravel/ui` just incase you don't have any other auth mechanism, this does not mean you have to use `laravel/ui`.

## Installation

You can install the package via composer:

```bash
composer require flightsadmin/livewire-crud
```

## Usage

After running `composer require flightsadmin/livewire-crud` command just run:

```bash
php artisan crud:install
```
**This command will perfom below actions:

    * Compile css/js based on `bootstrap and fontawesome/free`.
    * Run `npm install && run dev`
    * Flush *node_modules* files from you folder.

Then generate Crud by:

```bash
php artisan crud:generate {table-name}
```
**This command will generate:

    * Livewire Component.
    * Model.
    * Views.    
    * Factory.
    
**Remember to customise your genertaed factories and migrations if you need to use them later

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email george@flightsadmin.com instead of using the issue tracker.

## Credits

- [George Chitechi](https://github.com/flightsadmin)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
