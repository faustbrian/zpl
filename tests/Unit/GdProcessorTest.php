<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Zpl\Images\GdProcessor;
use Cline\Zpl\Images\ImageProcessorOption;
use Cline\Zpl\Settings\ConverterSettings;
use Cline\Zpl\Settings\ImageScale;

function gdProcessorFixture(string $filename): string
{
    return testUtils(verboseLogs: false)->fileGetContents(
        testUtils(verboseLogs: false)->testData($filename),
    );
}

function inlinePng(): string
{
    $image = imagecreatetruecolor(2, 1);
    imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
    imagesetpixel($image, 0, 0, imagecolorallocate($image, 0, 0, 0));

    ob_start();
    imagepng($image);

    return (string) ob_get_clean();
}

test('it reads image dimensions and pixel colors', function (): void {
    $processor = new GdProcessor(
        new ConverterSettings(),
    )->readBlob(inlinePng());

    expect($processor->width())->toBe(2)
        ->and($processor->height())->toBe(1)
        ->and($processor->isPixelBlack(0, 0))->toBeTrue()
        ->and($processor->isPixelBlack(1, 0))->toBeFalse()
        ->and($processor->processorType())->toBe(ImageProcessorOption::Gd);
});

test('it converts paletted images to true color', function (): void {
    $processor = new GdProcessor(
        new ConverterSettings(),
    )->readBlob(
        gdProcessorFixture('ups-label-as-gif.gif'),
    );

    expect($processor->width())->toBeGreaterThan(0)
        ->and($processor->height())->toBeGreaterThan(0);
});

test('it scales images using fill dimensions', function (): void {
    $processor = new GdProcessor(
        new ConverterSettings(
            scale: ImageScale::Fill,
            labelWidth: 4,
            labelHeight: 3,
        ),
    )->readBlob(inlinePng());

    $processor->scaleImage();

    expect($processor->width())->toBe(4)
        ->and($processor->height())->toBe(3);
});

test('it scales images using best fit dimensions', function (): void {
    $processor = new GdProcessor(
        new ConverterSettings(
            scale: ImageScale::Cover,
            labelWidth: 4,
            labelHeight: 4,
        ),
    )->readBlob(inlinePng());

    $processor->scaleImage();

    expect($processor->width())->toBe(4)
        ->and($processor->height())->toBe(2);
});

test('it skips scaling when resizing is disabled', function (): void {
    $processor = new GdProcessor(
        new ConverterSettings(
            scale: ImageScale::None,
            labelWidth: 10,
            labelHeight: 10,
        ),
    )->readBlob(inlinePng());

    $processor->scaleImage();

    expect($processor->width())->toBe(2)
        ->and($processor->height())->toBe(1);
});

test('it rotates images when configured', function (): void {
    $processor = new GdProcessor(
        new ConverterSettings(
            rotateDegrees: 90,
        ),
    )->readBlob(inlinePng());

    $processor->rotateImage();

    expect($processor->width())->toBe(1)
        ->and($processor->height())->toBe(2);
});

test('it skips rotation when no angle is configured', function (): void {
    $processor = new GdProcessor(
        new ConverterSettings(),
    )->readBlob(inlinePng());

    $processor->rotateImage();

    expect($processor->width())->toBe(2)
        ->and($processor->height())->toBe(1);
});
