<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit;

use Cline\Zpl\Images\ImageProcessorOption;
use Cline\Zpl\Images\ImagickProcessor;
use Cline\Zpl\Settings\ConverterSettings;
use Cline\Zpl\Settings\ImageScale;
use Imagick;
use ImagickDraw;
use ImagickPixel;

use function expect;
use function test;
use function testUtils;

function imagickProcessorFixture(string $filename): string
{
    return testUtils(verboseLogs: false)->fileGetContents(
        testUtils(verboseLogs: false)->testData($filename),
    );
}

function inlineImagickImage(): Imagick
{
    $image = new Imagick();
    $image->newImage(2, 1, new ImagickPixel('white'));

    $draw = new ImagickDraw();
    $draw->setFillColor(
        new ImagickPixel('black'),
    );
    $draw->point(0, 0);

    $image->drawImage($draw);
    $image->setImageFormat('png');

    return $image;
}

test('it reads image dimensions and pixel colors', function (): void {
    $processor = new ImagickProcessor(inlineImagickImage(), new ConverterSettings());

    expect($processor->width())->toBe(2)
        ->and($processor->height())->toBe(1)
        ->and($processor->isPixelBlack(0, 0))->toBeTrue()
        ->and($processor->isPixelBlack(1, 0))->toBeFalse()
        ->and($processor->processorType())->toBe(ImageProcessorOption::Imagick);
});

test('it reads raw image blobs and thresholds them', function (): void {
    $processor = new ImagickProcessor(
        new Imagick(),
        new ConverterSettings(),
    );
    $processor->readBlob(imagickProcessorFixture('duck.png'));

    expect($processor->width())->toBeGreaterThan(0)
        ->and($processor->height())->toBeGreaterThan(0);
});

test('it scales images with fill mode', function (): void {
    $image = inlineImagickImage();
    $processor = new ImagickProcessor($image, new ConverterSettings(
        scale: ImageScale::Fill,
        labelWidth: 4,
        labelHeight: 3,
    ));

    $processor->scaleImage();

    expect($processor->width())->toBe(4)
        ->and($processor->height())->toBe(3);
});

test('it scales images with best fit mode', function (): void {
    $image = inlineImagickImage();
    $processor = new ImagickProcessor($image, new ConverterSettings(
        scale: ImageScale::Cover,
        labelWidth: 4,
        labelHeight: 4,
    ));

    $processor->scaleImage();

    expect($processor->width())->toBe(4)
        ->and($processor->height())->toBe(2);
});

test('it skips scaling when resizing is disabled', function (): void {
    $image = inlineImagickImage();
    $processor = new ImagickProcessor($image, new ConverterSettings(
        scale: ImageScale::None,
        labelWidth: 8,
        labelHeight: 8,
    ));

    $processor->scaleImage();

    expect($processor->width())->toBe(2)
        ->and($processor->height())->toBe(1);
});

test('it rotates images when configured', function (): void {
    $image = inlineImagickImage();
    $processor = new ImagickProcessor($image, new ConverterSettings(
        rotateDegrees: 90,
    ));

    $processor->rotateImage();

    expect($processor->width())->toBe(1)
        ->and($processor->height())->toBe(2);
});

test('it skips rotation when no angle is configured', function (): void {
    $image = inlineImagickImage();
    $processor = new ImagickProcessor($image, new ConverterSettings());

    $processor->rotateImage();

    expect($processor->width())->toBe(2)
        ->and($processor->height())->toBe(1);
});
