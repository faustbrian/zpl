<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Zpl\Images\GdProcessor;
use Cline\Zpl\Images\ImageProcessorInterface;
use Cline\Zpl\Images\ImageProcessorOption;
use Cline\Zpl\Images\ImagickProcessor;
use Cline\Zpl\ImageToZplConverter;
use Cline\Zpl\PdfToZplConverter;
use Cline\Zpl\Settings\ConverterSettings;

test('image processor option is an enum', function (): void {
    expect(
        new ReflectionClass(ImageProcessorOption::class)->isEnum(),
    )->toBeTrue();
});

test('image processor option has gd and imagick cases', function (): void {
    expect(enum_exists(ImageProcessorOption::class))->toBeTrue()
        ->and(ImageProcessorOption::Gd->name)->toBe('Gd')
        ->and(ImageProcessorOption::Imagick->name)->toBe('Imagick');
});

test('processor cases create the expected processor types', function (): void {
    $settings = new ConverterSettings();
    $gdProcessor = ImageProcessorOption::Gd->processor($settings);
    $imagickProcessor = ImageProcessorOption::Imagick->processor($settings);

    expect($gdProcessor)->toBeInstanceOf(ImageProcessorInterface::class)
        ->and($gdProcessor)->toBeInstanceOf(GdProcessor::class)
        ->and($imagickProcessor)->toBeInstanceOf(ImageProcessorInterface::class)
        ->and($imagickProcessor)->toBeInstanceOf(ImagickProcessor::class);
});

test('configured processors report their processor type', function (): void {
    expect(
        new ConverterSettings(imageProcessorOption: ImageProcessorOption::Gd)->imageProcessor->processorType(),
    )->toBe(ImageProcessorOption::Gd)
        ->and(
            new ConverterSettings(imageProcessorOption: ImageProcessorOption::Imagick)->imageProcessor->processorType(),
        )->toBe(ImageProcessorOption::Imagick);
});

test('processor factory returns fresh instances', function (): void {
    $settings = new ConverterSettings();
    $first = ImageProcessorOption::Gd->processor($settings);
    $second = ImageProcessorOption::Gd->processor($settings);

    expect($first)->not->toBe($second);
});

test('different processor types create different instances', function (): void {
    $settings = new ConverterSettings();
    $gdProcessor = ImageProcessorOption::Gd->processor($settings);
    $imagickProcessor = ImageProcessorOption::Imagick->processor($settings);

    expect($gdProcessor)->not->toBe($imagickProcessor)
        ->and($gdProcessor)->toBeInstanceOf(GdProcessor::class)
        ->and($imagickProcessor)->toBeInstanceOf(ImagickProcessor::class);
});

test('processor accepts custom converter settings', function (): void {
    $settings = new ConverterSettings(labelWidth: 600, labelHeight: 800, dpi: 300);

    expect(ImageProcessorOption::Gd->processor($settings))->toBeInstanceOf(ImageProcessorInterface::class)
        ->and(ImageProcessorOption::Imagick->processor($settings))->toBeInstanceOf(ImageProcessorInterface::class);
});

test('converter settings uses its configured processor option', function (): void {
    expect(
        new ConverterSettings(imageProcessorOption: ImageProcessorOption::Gd)->imageProcessor,
    )->toBeInstanceOf(GdProcessor::class)
        ->and(
            new ConverterSettings(imageProcessorOption: ImageProcessorOption::Imagick)->imageProcessor,
        )->toBeInstanceOf(ImagickProcessor::class);
});

test('default converter settings uses gd', function (): void {
    $settings = ConverterSettings::default();

    expect($settings->imageProcessor)->toBeInstanceOf(GdProcessor::class)
        ->and($settings->imageProcessor->processorType())->toBe(ImageProcessorOption::Gd);
});

test('enum cases are distinct and can be matched', function (): void {
    $results = array_map(
        static fn (ImageProcessorOption $option): string => match ($option) {
            ImageProcessorOption::Gd => 'gd',
            ImageProcessorOption::Imagick => 'imagick',
        },
        [ImageProcessorOption::Gd, ImageProcessorOption::Imagick],
    );

    expect(ImageProcessorOption::Gd)->not->toBe(ImageProcessorOption::Imagick)
        ->and($results)->toBe(['gd', 'imagick']);
});

test('imagick can convert images', function (): void {
    $utils = testUtils(verboseLogs: false);
    $converter = new ImageToZplConverter(
        new ConverterSettings(
            imageProcessorOption: ImageProcessorOption::Imagick,
            verboseLogs: false,
        ),
    );
    $pages = $converter->convertFromFile($utils->testData('duck.png'));

    expect($pages)->toHaveCount(1)
        ->and($pages[0])->toContain('^XA')
        ->and($pages[0])->toContain('^XZ')
        ->and($pages[0])->toContain('^GFA');
});

test('imagick can convert pdfs', function (): void {
    $utils = testUtils(verboseLogs: false);
    $converter = new PdfToZplConverter(
        new ConverterSettings(
            imageProcessorOption: ImageProcessorOption::Imagick,
            verboseLogs: false,
        ),
    );
    $pages = $converter->convertFromFile($utils->testData('endicia-shipping-label.pdf'));

    expect($pages)->toHaveCount(3);

    foreach ($pages as $page) {
        expect($page)->toContain('^XA')
            ->and($page)->toContain('^XZ')
            ->and($page)->toContain('^GFA');
    }
})->skip(!ghostscriptAvailable(), 'Ghostscript is required for PDF conversion tests.');

test('both processors produce valid zpl output', function (): void {
    $utils = testUtils(verboseLogs: false);
    $duck = $utils->testData('duck.png');
    $gdZpl = new ImageToZplConverter(
        new ConverterSettings(
            imageProcessorOption: ImageProcessorOption::Gd,
            verboseLogs: false,
        ),
    )->convertFromFile($duck)[0];
    $imagickZpl = new ImageToZplConverter(
        new ConverterSettings(
            imageProcessorOption: ImageProcessorOption::Imagick,
            verboseLogs: false,
        ),
    )->convertFromFile($duck)[0];

    expect($gdZpl)->toStartWith('^XA')
        ->toEndWith('^XZ')
        ->toContain('^GFA')
        ->not->toBeEmpty()
        ->and($imagickZpl)->toStartWith('^XA')
        ->toEndWith('^XZ')
        ->toContain('^GFA')
        ->not->toBeEmpty();
});
