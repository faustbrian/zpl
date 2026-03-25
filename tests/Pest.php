<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Zpl\Logger\LoggerFactory;
use Tests\Support\TestUtils;
use Tests\TestCase;

pest()->extend(TestCase::class)->in(__DIR__);

function testUtils(bool $verboseLogs = true): TestUtils
{
    return new TestUtils(
        $verboseLogs
            ? LoggerFactory::createColoredLogger()
            : LoggerFactory::createVoidLogger(),
    );
}

function ghostscriptAvailable(): bool
{
    $output = shell_exec('command -v gs');

    return is_string($output) && mb_trim($output) !== '';
}
