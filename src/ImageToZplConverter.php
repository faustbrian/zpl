<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Zpl;

use Cline\Zpl\Exceptions\AbstractPdfToZplException;
use Cline\Zpl\Exceptions\CompressedImageRowException;
use Cline\Zpl\Exceptions\EncodedImageRowException;
use Cline\Zpl\Exceptions\MissingImageRowByteException;
use Cline\Zpl\Exceptions\UnreadableInputFileException;
use Cline\Zpl\Images\ImageProcessorInterface;
use Cline\Zpl\Settings\ConverterSettings;
use Illuminate\Support\Collection;

use function array_pop;
use function bindec;
use function ceil;
use function chr;
use function file_get_contents;
use function floor;
use function mb_str_pad;
use function mb_str_split;
use function mb_strlen;
use function mb_substr;
use function preg_replace;
use function preg_replace_callback;
use function sprintf;
use function str_repeat;

/**
 * Convert an Image to Zpl
 *
 * @author Brian Faust <brian@cline.sh>
 * @see https://github.com/himansuahm/php-zpl-converter
 */
final class ImageToZplConverter implements ZplConverterServiceInterface
{
    public const string START_CMD = '^XA';

    public const string END_CMD = '^XZ';

    private const string ENCODE_CMD = '^GFA';

    public ConverterSettings $settings;

    public function __construct(
        ?ConverterSettings $settings = null,
    ) {
        $this->settings = $settings ?? new ConverterSettings();
    }

    public static function build(ConverterSettings $settings): self
    {
        return new self($settings);
    }

    public static function canConvert(): array
    {
        return ['png', 'gif'];
    }

    /**
     * @throws AbstractPdfToZplException
     */
    public function convertImageToZpl(ImageProcessorInterface $image): string
    {
        // Width in bytes
        $width = (int) ceil($image->width() / 8);
        $height = $image->height();
        $bitmap = '';
        $lastRow = null;

        for ($y = 0; $y < $height; ++$y) {
            $bytes = $this->buildBytesForRow($image, $y);
            $row = $this->compressBytesToHex($bytes);
            $bitmap .= $this->buildBitmapAdditionForRow($row, $lastRow, $y);
            $lastRow = $row;
        }

        return $this->buildFinalZplCommand($bitmap, width: $width, height: $height);
    }

    /**
     * This can just be a string (the first few bytes say if its a GIF or PNG or whatever)
     */
    public function rawImageToZpl(string $rawImage): string
    {
        $img = $this->loadFromRawImage($rawImage, $this->settings->imageProcessor);
        $img->scaleImage();

        return $this->convertImageToZpl($img);
    }

    public function convertFromBlob(string $rawData): array
    {
        return [$this->rawImageToZpl($rawData)];
    }

    public function convertFromFile(string $filepath): array
    {
        $rawData = file_get_contents($filepath);

        if (!$rawData) {
            throw UnreadableInputFileException::forPath($filepath);
        }

        return $this->convertFromBlob($rawData);
    }

    private function buildFinalZplCommand(string $bitmap, int $width, int $height): string
    {
        $byteCount = $width * $height;
        $parameters = new Collection([
            self::ENCODE_CMD,
            $byteCount,
            $byteCount,
            $width,
            $bitmap,
        ]);

        return self::START_CMD
            .$parameters->implode(',')
            .self::END_CMD;
    }

    /**
     * Convert bytes to hex and compress
     * @param array<string> $bytes
     */
    private function compressBytesToHex(array $bytes): string
    {
        return new Collection($bytes)
            ->map(fn ($byte) => sprintf('%02X', bindec($byte)))
            ->implode('');
    }

    /**
     * @return array<string>
     */
    private function buildBytesForRow(ImageProcessorInterface $image, int $y): array
    {
        $bits = '';

        // Create a binary string for the row
        for ($x = 0; $x < $image->width(); ++$x) {
            $bits .= $image->isPixelBlack($x, $y) ? '1' : '0';
        }

        // Convert bits to bytes
        $bytes = mb_str_split($bits, length: 8);
        $lastByte = array_pop($bytes);

        /** @var null|string $lastByte */
        if ($lastByte === null) {
            throw MissingImageRowByteException::forRow();
        }
        $bytes[] = mb_str_pad($lastByte, length: 8, pad_string: '0');

        return $bytes;
    }

    private function buildBitmapAdditionForRow(string $row, ?string $lastRow, int $y): string
    {
        if ($row === $lastRow) {
            return ':';
        }

        $encoded = preg_replace(['/0+$/', '/F+$/'], [',', '!'], $row);

        if ($encoded === null) {
            throw EncodedImageRowException::failedForRow($y);
        }

        return $this->compressRow($encoded);
    }

    /**
     * @param string $rawImage The binary data of an image saved as a string (can be GIF, PNG or JPEG)
     */
    private function loadFromRawImage(string $rawImage, ImageProcessorInterface $processor): ImageProcessorInterface
    {
        return $processor->readBlob($rawImage);
    }

    /**
     * Run Line Encoder (replace repeating characters)
     */
    private function compressRow(string $row): string
    {
        $replaced = preg_replace_callback('/(.)(\1{2,})/', fn ($matches) => $this->compressSequence($matches[0]), $row);

        if (!$replaced) {
            throw CompressedImageRowException::failed();
        }

        return $replaced;
    }

    private function compressSequence(string $sequence): string
    {
        $repeat = mb_strlen($sequence);
        $count = '';

        if ($repeat > 400) {
            $count .= str_repeat('z', (int) floor($repeat / 400));
            $repeat %= 400;
        }

        if ($repeat > 19) {
            /** @var int<1, 20> $twenties */
            $twenties = (int) floor($repeat / 20);

            /** @var int<103, 122> $characterCode */
            $characterCode = 102 + $twenties;
            $count .= chr($characterCode);
            $repeat %= 20;
        }

        if ($repeat > 0) {
            /** @var int<1, 19> $repeat */
            /** @var int<71, 89> $characterCode */
            $characterCode = 70 + $repeat;
            $count .= chr($characterCode);
        }

        return $count.mb_substr($sequence, 1, 1);
    }
}
