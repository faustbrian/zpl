<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Zpl\Images;

use Cline\Zpl\Exceptions\GdImageCreateException;
use Cline\Zpl\Exceptions\GdImageReadException;
use Cline\Zpl\Exceptions\GdImageRotationException;
use Cline\Zpl\Exceptions\GdTrueColorConversionException;
use Cline\Zpl\Exceptions\ImageTooSmallException;
use Cline\Zpl\Settings\ConverterSettings;
use GdImage;

use function imagecolorat;
use function imagecopyresampled;
use function imagecreatefromstring;
use function imagecreatetruecolor;
use function imagepalettetotruecolor;
use function imagerotate;
use function imagesx;
use function imagesy;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class GdProcessor implements ImageProcessorInterface
{
    private GdImage $img;

    public function __construct(
        private readonly ConverterSettings $settings,
    ) {}

    public function width(): int
    {
        return imagesx($this->img);
    }

    public function height(): int
    {
        return imagesy($this->img);
    }

    public function isPixelBlack(int $x, int $y): bool
    {
        return (imagecolorat($this->img, $x, $y) & 0xFF) < 127;
    }

    public function readBlob(string $data): static
    {
        $img = imagecreatefromstring($data);

        if ($img === false) {
            throw GdImageReadException::fromBlob();
        }
        $this->img = $img;

        if (imagepalettetotruecolor($this->img) === false) {
            throw GdTrueColorConversionException::failed();
        }

        return $this;
    }

    public function scaleImage(): static
    {
        if (!$this->settings->scale->shouldResize() || $this->width() === $this->settings->labelWidth) {
            return $this;
        }

        $srcWidth = imagesx($this->img);
        $srcHeight = imagesy($this->img);

        $dstWidth = $this->settings->labelWidth;
        $dstHeight = $this->settings->labelHeight;

        if ($this->settings->scale->isBestFit()) {
            $aspectRatio = $srcWidth / $srcHeight;

            if ($srcWidth > $srcHeight) {
                $dstHeight = (int) ($dstWidth / $aspectRatio);
            } else {
                $dstWidth = (int) ($dstHeight * $aspectRatio);
            }
        }

        if ($dstWidth < 1 || $dstHeight < 1) {
            throw ImageTooSmallException::forScaling();
        }

        $scaledImg = imagecreatetruecolor($dstWidth, $dstHeight);

        if ($scaledImg === false) {
            throw GdImageCreateException::forScaledImage();
        }

        imagecopyresampled(
            $scaledImg,
            $this->img,
            dst_x: 0,
            dst_y: 0,
            src_x: 0,
            src_y: 0,
            dst_width: $dstWidth,
            dst_height: $dstHeight,
            src_width: $srcWidth,
            src_height: $srcHeight,
        );

        $this->img = $scaledImg;

        return $this;
    }

    public function rotateImage(): static
    {
        if (!$this->settings->rotateDegrees) {
            return $this;
        }

        $img = imagerotate($this->img, $this->settings->rotateDegrees, background_color: 0);

        if ($img === false) {
            throw GdImageRotationException::failed();
        }
        $this->img = $img;

        return $this;
    }

    public function processorType(): ImageProcessorOption
    {
        return ImageProcessorOption::Gd;
    }
}
