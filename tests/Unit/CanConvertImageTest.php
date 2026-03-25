<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Zpl\ImageToZplConverter;
use Cline\Zpl\Settings\ConverterSettings;

test('it can convert the duck image', function (): void {
    $utils = testUtils();
    $converter = new ImageToZplConverter(
        new ConverterSettings(verboseLogs: true, logger: $utils->logger),
    );
    $pages = $converter->convertFromFile($utils->testData('duck.png'));

    expect($pages)->toHaveCount(1)
        ->and($pages)->toBe($utils->loadExpectedPages('expected_duck', count($pages)));
});
