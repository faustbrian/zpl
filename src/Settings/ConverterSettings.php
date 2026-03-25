<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Zpl\Settings;

use Cline\Zpl\Exceptions\ImagickPdfFormatMissingException;
use Cline\Zpl\Exceptions\MissingGdExtensionException;
use Cline\Zpl\Exceptions\MissingImagickExtensionException;
use Cline\Zpl\Images\ImageProcessorInterface;
use Cline\Zpl\Images\ImageProcessorOption;
use Cline\Zpl\Logger\LoggerFactory;
use Imagick;
use Psr\Log\LoggerInterface;
use Stringable;

use function array_search;
use function extension_loaded;
use function is_string;
use function json_encode;

/**
 * Settings for the PDF to ZPL conversion * @author Brian Faust <brian@cline.sh>
 */
final class ConverterSettings
{
    public const int DEFAULT_LABEL_WIDTH = 812;

    public const int DEFAULT_LABEL_HEIGHT = 1_218;

    public const int DEFAULT_LABEL_DPI = 203;

    /**
     * The logger to use for `verboseLogs`
     * If using Laravel pass: `logger()`
     */
    public LoggerInterface $logger;

    /**
     * How the image should be scaled to fit on the label
     */
    public readonly ImageScale $scale;

    /**
     * Dots Per Inch of the desired Label
     */
    public readonly int $dpi;

    /**
     * The width in Pixels of your label
     */
    public readonly int $labelWidth;

    /**
     * The height in Pixels of your label
     */
    public readonly int $labelHeight;

    /**
     * The format to encode the image with
     */
    public readonly string $imageFormat;

    /**
     * How many degrees to rotate the label. Used for landscape PDFs
     */
    public readonly ?int $rotateDegrees;

    /**
     * The Image Processing backend to use (example: imagick or GD)
     */
    public readonly ImageProcessorInterface $imageProcessor;

    /**
     * Log each step of the process
     */
    public readonly bool $verboseLogs;

    public function __construct(
        ImageScale $scale = ImageScale::Cover,
        int $dpi = self::DEFAULT_LABEL_DPI,
        int $labelWidth = self::DEFAULT_LABEL_WIDTH,
        int $labelHeight = self::DEFAULT_LABEL_HEIGHT,
        string $imageFormat = 'png',
        ImageProcessorOption $imageProcessorOption = ImageProcessorOption::Gd,
        ?int $rotateDegrees = null,
        bool $verboseLogs = false,
        ?LoggerInterface $logger = null,
    ) {
        $this->scale = $scale;
        $this->dpi = $dpi;
        $this->labelWidth = $labelWidth;
        $this->labelHeight = $labelHeight;
        $this->imageFormat = $imageFormat;
        $this->rotateDegrees = $rotateDegrees;
        $this->verboseLogs = $verboseLogs;
        $this->logger = $logger ?: LoggerFactory::createVoidLogger();
        $this->verifyDependencies($imageProcessorOption);

        $this->imageProcessor = $imageProcessorOption->processor($this);
    }

    public static function default(): self
    {
        return new self();
    }

    public function log(mixed ...$messages): void
    {
        if (!$this->verboseLogs) {
            return;
        }

        foreach ($messages as $message) {
            $message = is_string($message) || $message instanceof Stringable
                ? (string) $message
                : (string) json_encode($message);
            $this->logger->debug($message);
        }
    }

    private function verifyDependencies(ImageProcessorOption $option): void
    {
        if (!extension_loaded('gd') && $option === ImageProcessorOption::Gd) {
            throw MissingGdExtensionException::installOrUseImagick();
        }

        if (!extension_loaded('imagick')) {
            throw MissingImagickExtensionException::install();
        }

        $formats = Imagick::queryFormats();

        if (array_search('PDF', $formats, true) === false) {
            throw ImagickPdfFormatMissingException::installGhostscript();
        }
    }
}
