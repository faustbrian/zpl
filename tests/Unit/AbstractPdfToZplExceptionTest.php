<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Zpl\Exceptions\ZplExceptionInterface;
use Tests\Exceptions\TestDataReadException;
use Tests\Exceptions\TestPackageException;

test('it stores context and previous exceptions', function (): void {
    $previous = TestDataReadException::forPath('inner');
    $exception = new TestPackageException(
        message: 'Conversion failed',
        code: 123,
        previous: $previous,
        context: ['page' => 2, 'source' => 'label.pdf'],
    );

    expect($exception->getMessage())->toBe('Conversion failed')
        ->and($exception->getCode())->toBe(123)
        ->and($exception->getPrevious())->toBe($previous)
        ->and($exception->context)->toBe(['page' => 2, 'source' => 'label.pdf']);
});

test('it formats itself as a string', function (): void {
    $exception = new TestPackageException('No converter');

    expect((string) $exception)->toBe(TestPackageException::class.': No converter');
});

test('it marks package exceptions with the package marker interface', function (): void {
    expect(
        new TestPackageException('base'),
    )->toBeInstanceOf(ZplExceptionInterface::class);
});
