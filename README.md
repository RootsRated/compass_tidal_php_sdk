# Compass Tidal PHP SDK

[![Code Climate](https://codeclimate.com/repos/58d2d7a042f14a02710000c0/badges/3f57be90855275bfa1a4/gpa.svg)](https://codeclimate.com/repos/58d2d7a042f14a02710000c0/feed)
[![Test Coverage](https://codeclimate.com/repos/58d2d7a042f14a02710000c0/badges/3f57be90855275bfa1a4/coverage.svg)](https://codeclimate.com/repos/58d2d7a042f14a02710000c0/coverage)
[![CircleCI](https://circleci.com/gh/RootsRated/compass_tidal_php_sdk/tree/master.svg?style=svg&circle-token=9ca7bfabe320d6b4a14e8cecc24457f35eb099b0)](https://circleci.com/gh/RootsRated/compass_tidal_php_sdk/tree/master)

A full-featured SDK, written in PHP, to allow simplified access to
RootsRated Media's Compass platform.

## Requirements

This SDK requires a minimum of PHP 5.6.x to work.

## Installation

Simply add a dependency on rootsrated_media/compass_tidal_sdk to your
project's composer.json file if you use Composer to manage the
dependencies of your project:

    composer require rootsrated_media/compass_tidal_sdk ^1.0

Check out Composer's [Getting
Started](https://getcomposer.org/doc/00-intro.md) guide for more
information.

## Configuration

## Development

### Tests

Make sure you have the following dependencies:

 - Composer
 - PHPUnit 5.7+

Run the following from the command line:

    phpunit --bootstrap SDK/RootsratedSDK.php tests/SDKtest

### Contributing

1. Fork it ( https://github.com/RootsRated/compass_tidal_php_sdk/fork )
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Push to the branch (`git push origin my-new-feature`)
5. Create a new Pull Request
