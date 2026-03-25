[![GitHub Workflow Status][ico-tests]][link-tests]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

------

# zpl

Convert PDFs and images into ZPL output for Zebra printers.

## Requirements

> **Requires [PHP 8.4+](https://php.net/releases/)**

## Installation

```bash
composer require cline/zpl
```

## Usage

```php
use Cline\Zpl\PdfToZplConverter;
use Cline\Zpl\Settings\ConverterSettings;

$converter = new PdfToZplConverter(new ConverterSettings());
$pages = $converter->convertFromFile('/path/to/label.pdf');
```

## Errors

The package throws specific exceptions that all implement
`Cline\Zpl\Exceptions\ZplException`, so consumers can catch either granular
failure types or the package-wide marker interface.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please use the [GitHub security reporting form][link-security] rather than the issue queue.

## Credits

- [Brian Faust][link-maintainer]
- [All Contributors][link-contributors]

## License

The MIT License. Please see [License File](LICENSE.md) for more information.

[ico-tests]: https://github.com/faustbrian/zpl/actions/workflows/quality-assurance.yaml/badge.svg
[ico-version]: https://img.shields.io/packagist/v/cline/zpl.svg
[ico-license]: https://img.shields.io/badge/License-MIT-green.svg
[ico-downloads]: https://img.shields.io/packagist/dt/cline/zpl.svg

[link-tests]: https://github.com/faustbrian/zpl/actions
[link-packagist]: https://packagist.org/packages/cline/zpl
[link-downloads]: https://packagist.org/packages/cline/zpl
[link-security]: https://github.com/faustbrian/zpl/security
[link-maintainer]: https://github.com/faustbrian
[link-contributors]: ../../contributors
