<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Zpl\Settings\ConverterSettings;
use Cline\Zpl\ZplConverterFactory;

test('it can use the factory for images', function (): void {
    $utils = testUtils();
    $duck = $utils->testData('duck.png');
    $converter = ZplConverterFactory::converterFromFile($duck, new ConverterSettings(verboseLogs: false, logger: $utils->logger));
    $pages = $converter->convertFromFile($duck);

    expect($pages)->toHaveCount(1)
        ->and($utils->percentSimilarToExpected($pages, 'expected_duck', 'can use factory for image'))->toBeGreaterThan(95);
});

test('it can use the factory for pdfs', function (): void {
    $utils = testUtils();
    $pdf = $utils->testData('endicia-shipping-label.pdf');
    $converter = ZplConverterFactory::converterFromFile($pdf, new ConverterSettings(verboseLogs: false));
    $pages = $converter->convertFromFile($pdf);

    expect($pages)->toHaveCount(3)
        ->and($utils->percentSimilarToExpected($pages, 'expected_label', 'can use factory for pdf'))->toBeGreaterThan(95);
})->skip(!ghostscriptAvailable(), 'Ghostscript is required for PDF conversion tests.');
