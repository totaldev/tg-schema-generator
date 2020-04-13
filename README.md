# SchemaGenerator

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Code Quality][ico-quality]][link-scrutinizer]
[![Total Downloads][ico-downloads]][link-downloads]

[![Email][ico-email]][link-email]

The PHP TD Gram Schema Generator tool to generate Schema classes from [td_api.tl][link-td-api] file.


## Install

Via Composer

```bash
$ composer require phptdgram/schema-generator
```

## Using

```bash
$ git clone git@github.com:phptdgram/schema.git shema
$ ./bin/schema-generator
$ cd ./schema
$ composer install
$ composer cs-fix
```

## Testing

Run PHP style checker

```bash
$ composer cs-check
```

Run PHP style fixer

```bash
$ composer cs-fix
```

Run all continuous integration tests

```bash
$ composer ci-run
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.


## License

Please see [License File](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/phptdgram/schema-generator.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/com/phptdgram/schema-generator/master.svg?style=flat-square
[ico-quality]: https://img.shields.io/scrutinizer/quality/g/phptdgram/schema-generator?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/phptdgram/schema-generator.svg?style=flat-square
[ico-email]: https://img.shields.io/badge/email-aurimas@niekis.lt-blue.svg?style=flat-square

[link-travis]: https://travis-ci.com/phptdgram/schema-generator
[link-packagist]: https://packagist.org/packages/phptdgram/schema-generator
[link-scrutinizer]: https://scrutinizer-ci.com/g/phptdgram/schema-generator
[link-downloads]: https://packagist.org/packages/phptdgram/schema-generator/stats
[link-td-api]: https://github.com/tdlib/td/blob/master/td/generate/scheme/td_api.tl
[link-email]: mailto:aurimas@niekis.lt
