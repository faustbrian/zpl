<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Zpl\Images\GdProcessor;
use Cline\Zpl\Settings\ConverterSettings;
use Cline\Zpl\Settings\ImageScale;
use Psr\Log\LoggerInterface;

test('it exposes default settings values', function (): void {
    $settings = ConverterSettings::default();

    expect($settings->scale)->toBe(ImageScale::Cover)
        ->and($settings->dpi)->toBe(ConverterSettings::DEFAULT_LABEL_DPI)
        ->and($settings->labelWidth)->toBe(ConverterSettings::DEFAULT_LABEL_WIDTH)
        ->and($settings->labelHeight)->toBe(ConverterSettings::DEFAULT_LABEL_HEIGHT)
        ->and($settings->imageFormat)->toBe('png')
        ->and($settings->rotateDegrees)->toBeNull()
        ->and($settings->verboseLogs)->toBeFalse()
        ->and($settings->imageProcessor)->toBeInstanceOf(GdProcessor::class);
});

test('it keeps a provided logger and configuration', function (): void {
    $logger = new class() implements LoggerInterface
    {
        public array $messages = [];

        public function emergency(Stringable|string $message, array $context = []): void {}

        public function alert(Stringable|string $message, array $context = []): void {}

        public function critical(Stringable|string $message, array $context = []): void {}

        public function error(Stringable|string $message, array $context = []): void {}

        public function warning(Stringable|string $message, array $context = []): void {}

        public function notice(Stringable|string $message, array $context = []): void {}

        public function info(Stringable|string $message, array $context = []): void {}

        public function debug(Stringable|string $message, array $context = []): void
        {
            $this->messages[] = (string) $message;
        }

        public function log($level, Stringable|string $message, array $context = []): void {}
    };

    $settings = new ConverterSettings(
        scale: ImageScale::Fill,
        dpi: 300,
        labelWidth: 400,
        labelHeight: 600,
        imageFormat: 'gif',
        rotateDegrees: 180,
        verboseLogs: true,
        logger: $logger,
    );

    $settings->log(
        'plain message',
        new class() implements Stringable
        {
            public function __toString(): string
            {
                return 'stringable message';
            }
        },
        ['page' => 2],
    );

    expect($settings->logger)->toBe($logger)
        ->and($settings->scale)->toBe(ImageScale::Fill)
        ->and($settings->dpi)->toBe(300)
        ->and($settings->labelWidth)->toBe(400)
        ->and($settings->labelHeight)->toBe(600)
        ->and($settings->imageFormat)->toBe('gif')
        ->and($settings->rotateDegrees)->toBe(180)
        ->and($settings->verboseLogs)->toBeTrue()
        ->and($logger->messages)->toBe([
            'plain message',
            'stringable message',
            '{"page":2}',
        ]);
});

test('it ignores log messages when verbose logging is disabled', function (): void {
    $logger = new class() implements LoggerInterface
    {
        public int $debugCalls = 0;

        public function emergency(Stringable|string $message, array $context = []): void {}

        public function alert(Stringable|string $message, array $context = []): void {}

        public function critical(Stringable|string $message, array $context = []): void {}

        public function error(Stringable|string $message, array $context = []): void {}

        public function warning(Stringable|string $message, array $context = []): void {}

        public function notice(Stringable|string $message, array $context = []): void {}

        public function info(Stringable|string $message, array $context = []): void {}

        public function debug(Stringable|string $message, array $context = []): void
        {
            ++$this->debugCalls;
        }

        public function log($level, Stringable|string $message, array $context = []): void {}
    };

    $settings = new ConverterSettings(verboseLogs: false, logger: $logger);
    $settings->log('message that should be skipped');

    expect($logger->debugCalls)->toBe(0);
});
