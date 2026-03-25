<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Zpl\PdfToZplConverter;
use Cline\Zpl\Settings\ConverterSettings;
use Illuminate\Support\Collection;

test('it reports the pdf extension it can convert', function (): void {
    expect(PdfToZplConverter::canConvert())->toBe(['pdf']);
});

test('it builds a converter with the provided settings', function (): void {
    $settings = new ConverterSettings(verboseLogs: true);
    $converter = PdfToZplConverter::build($settings);

    expect($converter)->toBeInstanceOf(PdfToZplConverter::class)
        ->and($converter->settings)->toBe($settings);
});

test('it converts pdf files to image blobs', function (): void {
    $utils = testUtils(verboseLogs: false);
    $converter = new PdfToZplConverter(
        new ConverterSettings(verboseLogs: false),
    );
    $pdfData = $utils->fileGetContents($utils->testData('endicia-shipping-label.pdf'));

    $images = $converter->pdfToImages($pdfData);

    expect($images)->toBeInstanceOf(Collection::class)
        ->and($images)->toHaveCount(3)
        ->and($images->every(
            static fn (string $image): bool => str_starts_with($image, "\x89PNG\r\n\x1a\n"),
        ))->toBeTrue();
})->skip(!ghostscriptAvailable(), 'Ghostscript is required for PDF conversion tests.');

test('it converts pdf blobs to zpl arrays', function (): void {
    $utils = testUtils(verboseLogs: false);
    $converter = new PdfToZplConverter(
        new ConverterSettings(verboseLogs: false),
    );
    $pdfData = $utils->fileGetContents($utils->testData('endicia-shipping-label.pdf'));

    $pages = $converter->convertFromBlob($pdfData);

    expect($pages)->toHaveCount(3)
        ->and($pages[0])->toContain('^XA')
        ->and($pages[0])->toContain('^GFA')
        ->and($pages[0])->toContain('^XZ');
})->skip(!ghostscriptAvailable(), 'Ghostscript is required for PDF conversion tests.');
