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
$ composer require totaldev/tg-schema-generator
```

## Using

```bash
$ git clone git@github.com:totaldev/schema.git schema
$ cd ./schema
$ composer install
$ ./bin/schema-generator {$pathToFile:td_api.tl}
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

[ico-version]: https://img.shields.io/packagist/v/totaldev/tg-schema-generator.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/com/totaldev/tg-schema-generator/master.svg?style=flat-square
[ico-quality]: https://img.shields.io/scrutinizer/quality/g/totaldev/tg-schema-generator?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/totaldev/tg-schema-generator.svg?style=flat-square
[ico-email]: https://img.shields.io/badge/email-aurimas@niekis.lt-blue.svg?style=flat-square

[link-travis]: https://travis-ci.com/totaldev/tg-schema-generator
[link-packagist]: https://packagist.org/packages/totaldev/tg-schema-generator
[link-scrutinizer]: https://scrutinizer-ci.com/g/totaldev/tg-schema-generator
[link-downloads]: https://packagist.org/packages/totaldev/tg-schema-generator/stats
[link-td-api]: https://github.com/tdlib/td/blob/master/td/generate/scheme/td_api.tl
[link-email]: mailto:aurimas@niekis.lt
