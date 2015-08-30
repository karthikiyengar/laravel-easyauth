# Easy Auth

[![Software License][ico-license]](LICENSE.md)

Quick RESTful authentication for registration and login. Includes support for Facebook and Google+.

## Install

Via Composer

Add this repository to your composer.json
```
  "repositories": [
      {
        "type" : "vcs",
        "url" : "https://github.com/karthikiyengar/easy-auth.git"
      }
    ]
```
Then require the package

``` bash
$ composer require paverblock/easyauth
```

## Usage

Register the following providers in your app.php

```
Paverblock\Easyauth\Providers\EasyAuthServiceProvider::class
```

Since this application depends on JWT Tokens, you need to add this middleware to your Kernel.php:

```
'jwt.auth' => \Tymon\JWTAuth\Providers\JWTAuthServiceProvider::class,
```
Then in your terminal:

```
php artisan vendor:publish
```

Run database migrations as well:

```
php artisan migrate
```

## To Do

- Better exception handling in some cases
- Test Cases
- Derive messages from config file
- Use transactions while performing inserts
- Create a proper User model migration
- Lumen Support
- Better Documentation

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/league/:package_name.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/thephpleague/:package_name/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/thephpleague/:package_name.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/thephpleague/:package_name.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/league/:package_name.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/league/:package_name
[link-travis]: https://travis-ci.org/thephpleague/:package_name
[link-scrutinizer]: https://scrutinizer-ci.com/g/thephpleague/:package_name/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/thephpleague/:package_name
[link-downloads]: https://packagist.org/packages/league/:package_name
[link-author]: https://github.com/:author_username
[link-contributors]: ../../contributors
