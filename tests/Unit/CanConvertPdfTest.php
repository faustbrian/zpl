<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Zpl\PdfToZplConverter;
use Cline\Zpl\Settings\ConverterSettings;

test('it can convert the endicia pdf', function (): void {
    $utils = testUtils();
    $converter = new PdfToZplConverter(
        new ConverterSettings(verboseLogs: false, logger: $utils->logger),
    );
    $pages = $converter->convertFromFile($utils->testData('endicia-shipping-label.pdf'));

    expect($pages)->toHaveCount(3)
        ->and($utils->percentSimilarToExpected($pages, 'expected_label', 'can convert endicia pdf'))->toBeGreaterThan(95);
})->skip(!ghostscriptAvailable(), 'Ghostscript is required for PDF conversion tests.');

test('it can convert the donkey pdf', function (): void {
    $utils = testUtils();
    $converter = new PdfToZplConverter(
        new ConverterSettings(verboseLogs: false),
    );
    $pages = $converter->convertFromFile($utils->testData('donkey.pdf'));

    expect($pages)->toHaveCount(9)
        ->and($utils->percentSimilarToExpected($pages, 'expected_donkey', 'can convert donkey'))->toBeGreaterThan(95);
})->skip(!ghostscriptAvailable(), 'Ghostscript is required for PDF conversion tests.');
