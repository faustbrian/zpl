<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Zpl\PdfToZplConverter;
use Cline\Zpl\Settings\ConverterSettings;

test('it can rotate a landscape pdf', function (): void {
    $utils = testUtils();
    $converter = new PdfToZplConverter(
        new ConverterSettings(
            verboseLogs: false,
            logger: $utils->logger,
            rotateDegrees: 90,
        ),
    );
    $pages = $converter->convertFromFile($utils->testData('usps-label-landscape.pdf'));

    expect($pages)->toHaveCount(4)
        ->and($utils->percentSimilarToExpected($pages, 'expected_usps_landscape', 'can rotate landscape'))->toBeGreaterThan(95);
})->skip(!ghostscriptAvailable(), 'Ghostscript is required for PDF conversion tests.');
