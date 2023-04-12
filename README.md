# Laravel Cities

[![Total Downloads](https://poser.pugx.org/vioms/laravel-cities/downloads.svg)](https://packagist.org/packages/vioms/laravel-cities)
[![Latest Stable Version](https://poser.pugx.org/vioms/laravel-cities/v/stable.svg)](https://packagist.org/packages/vioms/laravel-cities)
[![Latest Unstable Version](https://poser.pugx.org/vioms/laravel-cities/v/unstable.svg)](https://packagist.org/packages/vioms/laravel-cities)

Laravel Cities is a bundle for Laravel for all cities.

This package requires:
* vioms/laravel-countries

## Notice
The population of this package might take a few minute to a few hours depending on your installation.
The import file is 1.5 GB 

## Installation

Runs `composer require vioms/laravel-cities` to install the package

Run `composer update` to pull down the latest version of Cities.

## Model

You can start by publishing the configuration. This is an optional step, it contains the table name and does not need to be altered. If the default name `cities` suits you, leave it. Otherwise run the following command

    $ php artisan vendor:publish

You may now run it with the artisan migrate command:

    $ php artisan migrate
    $ php artisan cities:populate

After running this command the filled cities table will be available
