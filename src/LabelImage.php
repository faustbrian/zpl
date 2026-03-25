<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Zpl;

use Cline\Zpl\Exceptions\LabelImageDownloadException;
use Cline\Zpl\Settings\LabelDirection;
use GuzzleHttp\Client as GuzzleClient;

use function base64_encode;
use function file_put_contents;

/**
 * A binary PNG image of a ZPL label fetched from `labelary.com`
 * This is a great way to debug or give users a preview before printing
 *
 * Only 5 requests are allowed per second!
 * @author Brian Faust <brian@cline.sh>
 */
final class LabelImage
{
    public const string URL = 'https://api.labelary.com/v1/printers/8dpmm/labels';

    public string $image;

    private static GuzzleClient $httpClient;

    private static ?ImageToZplConverter $imageConverter = null;

    public function __construct(
        public readonly string $zpl,
        public readonly LabelDirection $direction = LabelDirection::Up,
        public readonly float $width = 4,
        public readonly float $height = 6,
    ) {
        self::$httpClient ??= new GuzzleClient();
        $this->download();
    }

    /**
     * Download and return a raw PNG as a string
     */
    public function download(): string
    {
        $headers = [
            'Accept' => 'image/png',
            'X-Rotation' => (string) $this->direction->toDegree(),
        ];

        $url = self::URL."/{$this->width}x{$this->height}/0/";

        $response = self::$httpClient->post($url, [
            'headers' => $headers,
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => $this->zpl,
                ],
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw LabelImageDownloadException::forStatusCode($response->getStatusCode());
        }

        $this->image = (string) $response->getBody();

        return $this->image;
    }

    /**
     * For use in HTML image tags. `<img src="{{ $label->asHtmlImage() }}" />`
     */
    public function asHtmlImage(): string
    {
        return 'data:image/png;base64,'.base64_encode($this->image);
    }

    /**
     * A raw binary data of the image. Can be saved to disk or uploaded
     */
    public function asRaw(): string
    {
        return $this->image;
    }

    /**
     * Use the binary form of this image in a ZPL statement
     * This bypasses the printer's font encoder allowing any
     * character / font
     */
    public function toZpl(): string
    {
        self::$imageConverter ??= new ImageToZplConverter();

        return self::$imageConverter->rawImageToZpl($this->image);
    }

    /**
     * Save the image to disk
     */
    public function saveAs(string $filepath): void
    {
        file_put_contents($filepath, $this->image);
    }
}
