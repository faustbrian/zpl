<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Zpl\Logger\LoggerFactory;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

test('logger factory methods return logger instances', function (): void {
    $colored = LoggerFactory::createColoredLogger();
    $echo = LoggerFactory::createEchoLogger();
    $void = LoggerFactory::createVoidLogger();

    expect($colored)->toBeInstanceOf(LoggerInterface::class)
        ->and($colored)->toBeInstanceOf(Logger::class)
        ->and($echo)->toBeInstanceOf(LoggerInterface::class)
        ->and($echo)->toBeInstanceOf(Logger::class)
        ->and($void)->toBeInstanceOf(LoggerInterface::class)
        ->and($void)->toBeInstanceOf(Logger::class);
});

test('logger factory methods accept custom names', function (): void {
    expect(LoggerFactory::createColoredLogger('custom-logger'))->toBeInstanceOf(Logger::class)
        ->and(LoggerFactory::createEchoLogger('custom-logger'))->toBeInstanceOf(Logger::class)
        ->and(LoggerFactory::createVoidLogger('custom-logger'))->toBeInstanceOf(Logger::class);
});

test('logger instances can log with and without context', function (): void {
    $loggers = [
        LoggerFactory::createColoredLogger(),
        LoggerFactory::createEchoLogger(),
        LoggerFactory::createVoidLogger(),
    ];

    foreach ($loggers as $logger) {
        $logger->debug('Debug message');
        $logger->info('Info message', ['key' => 'value', 'number' => 42]);
        $logger->warning('Warning message');
        $logger->error('Error message');
        $logger->emergency('Emergency message');
        $logger->alert('Alert message');
        $logger->critical('Critical message');
        $logger->notice('Notice message');
    }

    expect(true)->toBeTrue();
});

test('generic create returns the expected logger variants', function (): void {
    $colored = LoggerFactory::create('colored');
    $echo = LoggerFactory::create('echo');
    $void = LoggerFactory::create('void');
    $invalid = LoggerFactory::create('invalid-type');
    $default = LoggerFactory::create();

    expect($colored)->toBeInstanceOf(Logger::class)
        ->and($echo)->toBeInstanceOf(Logger::class)
        ->and($void)->toBeInstanceOf(Logger::class)
        ->and($invalid)->toBeInstanceOf(Logger::class)
        ->and($default)->toBeInstanceOf(Logger::class)
        ->and($colored)->not->toBe($echo)
        ->and($colored)->not->toBe($void)
        ->and($echo)->not->toBe($void);
});

test('factory methods are static', function (): void {
    $reflection = new ReflectionClass(LoggerFactory::class);

    expect($reflection->getMethod('createColoredLogger')->isStatic())->toBeTrue()
        ->and($reflection->getMethod('createEchoLogger')->isStatic())->toBeTrue()
        ->and($reflection->getMethod('createVoidLogger')->isStatic())->toBeTrue()
        ->and($reflection->getMethod('create')->isStatic())->toBeTrue();
});
