<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Zpl\LabelImage;
use Cline\Zpl\Settings\LabelDirection;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

beforeEach(function (): void {
    setLabelImageHttpClient(
        new Client(),
    );
    setLabelImageImageConverter(null);
});

function apiTestsEnabled(): bool
{
    return filter_var((string) getenv('ZPL_RUN_API_TESTS'), \FILTER_VALIDATE_BOOL);
}

function setLabelImageHttpClient(Client $client): void
{
    $property = new ReflectionProperty(LabelImage::class, 'httpClient');
    $property->setValue(null, $client);
}

function setLabelImageImageConverter(?object $converter): void
{
    $property = new ReflectionProperty(LabelImage::class, 'imageConverter');
    $property->setValue(null, $converter);
}

function labelImageFixturePng(): string
{
    return testUtils(verboseLogs: false)->fileGetContents(
        testUtils(verboseLogs: false)->testData('duck.png'),
    );
}

function mockLabelImageHttpClient(string $image, int $status = 200, array &$history = []): void
{
    $mock = new MockHandler([new Response($status, [], $image)]);
    $handlerStack = HandlerStack::create($mock);
    $handlerStack->push(Middleware::history($history));

    setLabelImageHttpClient(
        new Client([
            'handler' => $handlerStack,
            'http_errors' => false,
        ]),
    );
}

test('it can create a label image without the real api', function (): void {
    mockLabelImageHttpClient(labelImageFixturePng());

    $label = new LabelImage('^XA^FO50,50^ADN,36,20^FDTest Label^FS^XZ');

    expect($label->image)->toBe(labelImageFixturePng())
        ->and(mb_strlen($label->image))->toBeGreaterThan(100);
});

test('it sends the expected request to labelary', function (): void {
    $history = [];
    mockLabelImageHttpClient(labelImageFixturePng(), history: $history);

    new LabelImage(
        zpl: '^XA^FO50,50^ADN,36,20^FDTest Label^FS^XZ',
        direction: LabelDirection::Right,
        width: 2,
        height: 3,
    );

    $request = $history[0]['request'];

    expect((string) $request->getUri())->toBe(sprintf('%s/2x3/0/', LabelImage::URL))
        ->and($request->getHeaderLine('Accept'))->toBe('image/png')
        ->and($request->getHeaderLine('X-Rotation'))->toBe((string) LabelDirection::Right->toDegree())
        ->and((string) $request->getBody())->toContain('Test Label');
});

test('it can create a label image and hit the api', function (): void {
    $label = new LabelImage('^XA^FO50,50^ADN,36,20^FDTest Label^FS^XZ');

    expect($label->image)->not->toBeEmpty()
        ->and(mb_strlen($label->image))->toBeGreaterThan(100);
})->skip(!apiTestsEnabled(), 'Set ZPL_RUN_API_TESTS=true to run Labelary integration tests.')->group('api');

test('it downloads a valid png image', function (): void {
    $label = new LabelImage('^XA^FO50,50^ADN,36,20^FDTest Label^FS^XZ');
    $image = $label->asRaw();

    expect(mb_substr($image, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
})->skip(!apiTestsEnabled(), 'Set ZPL_RUN_API_TESTS=true to run Labelary integration tests.')->group('api');

test('it renders an html data uri', function (): void {
    $html = new LabelImage('^XA^FO50,50^ADN,36,20^FDTest Label^FS^XZ')->asHtmlImage();
    $base64 = mb_substr($html, mb_strlen('data:image/png;base64,'));

    expect($html)->toStartWith('data:image/png;base64,')
        ->and($base64)->not->toBeEmpty()
        ->and(base64_decode($base64, true))->not->toBeFalse();
})->skip(!apiTestsEnabled(), 'Set ZPL_RUN_API_TESTS=true to run Labelary integration tests.')->group('api');

test('it returns raw image data', function (): void {
    mockLabelImageHttpClient(labelImageFixturePng());
    $label = new LabelImage('^XA^FO50,50^ADN,36,20^FDTest Label^FS^XZ');

    expect($label->asRaw())->toBe($label->image);
});

test('it can save the image to a file', function (): void {
    mockLabelImageHttpClient(labelImageFixturePng());
    $label = new LabelImage('^XA^FO50,50^ADN,36,20^FDTest Label^FS^XZ');
    $tempFile = sys_get_temp_dir().'/test_label_image.png';

    try {
        $label->saveAs($tempFile);

        expect(file_exists($tempFile))->toBeTrue()
            ->and(file_get_contents($tempFile))->toBe($label->image)
            ->and(getimagesize($tempFile))->not->toBeFalse()
            ->and(array_key_exists('mime', getimagesize($tempFile)))->toBeTrue()
            ->and(getimagesize($tempFile)['mime'])->toBe('image/png');
    } finally {
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }
});

test('it renders an html data uri without the real api', function (): void {
    mockLabelImageHttpClient(labelImageFixturePng());
    $label = new LabelImage('^XA^FO50,50^ADN,36,20^FDTest Label^FS^XZ');

    expect($label->asHtmlImage())->toBe('data:image/png;base64,'.base64_encode($label->image));
});

test('it can convert the downloaded image back to zpl', function (): void {
    mockLabelImageHttpClient(labelImageFixturePng());
    $label = new LabelImage('^XA^FO50,50^ADN,36,20^FDTest Label^FS^XZ');

    expect($label->toZpl())->toContain('^XA')
        ->toContain('^GFA')
        ->toContain('^XZ');
});

test('it supports custom dimensions', function (): void {
    $label = new LabelImage(
        zpl: '^XA^FO50,50^ADN,36,20^FDTest Label^FS^XZ',
        width: 2,
        height: 3,
    );

    expect(mb_substr($label->image, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
})->skip(!apiTestsEnabled(), 'Set ZPL_RUN_API_TESTS=true to run Labelary integration tests.')->group('api');

test('it supports a custom direction', function (): void {
    $label = new LabelImage(
        zpl: '^XA^FO50,50^ADN,36,20^FDTest Label^FS^XZ',
        direction: LabelDirection::Right,
    );

    expect(mb_substr($label->image, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
})->skip(!apiTestsEnabled(), 'Set ZPL_RUN_API_TESTS=true to run Labelary integration tests.')->group('api');
