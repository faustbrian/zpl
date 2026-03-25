<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Zpl\Images;

use Cline\Zpl\Exceptions\AbstractPdfToZplException;
use Cline\Zpl\Exceptions\ImagickImageLoadException;
use Cline\Zpl\Settings\ConverterSettings;
use Imagick;
use ImagickPixel;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class ImagickProcessor implements ImageProcessorInterface
{
    public function __construct(
        private readonly Imagick $img,
        private readonly ConverterSettings $settings,
    ) {}

    public function width(): int
    {
        return $this->img->getImageWidth();
    }

    public function height(): int
    {
        return $this->img->getImageHeight();
    }

    public function isPixelBlack(int $x, int $y): bool
    {
        $pixel = $this->img->getImagePixelColor($x, $y);
        $color = $pixel->getColor();
        $avgColor = ($color['r'] + $color['g'] + $color['b']) / 3;

        return $avgColor < 0.5;
    }

    /**
     * @throws AbstractPdfToZplException
     */
    public function readBlob(string $data): static
    {
        $blob = $this->img->readImageBlob($data);

        if (!$blob) {
            throw ImagickImageLoadException::fromBlob();
        }

        $this->img->setImageColorspace(Imagick::COLORSPACE_RGB);
        $this->img->setImageFormat('png');

        $quantum = Imagick::getQuantum();
        $this->img->thresholdImage(0.5 * $quantum);

        return $this;
    }

    /**
     * Perform any necessary scaling on the image
     */
    public function scaleImage(): static
    {
        if (!$this->settings->scale->shouldResize() || $this->width() === $this->settings->labelWidth) {
            return $this;
        }

        $this->img->scaleImage(
            $this->settings->labelWidth,
            $this->settings->labelHeight,
            bestfit: $this->settings->scale->isBestFit(),
        );

        return $this;
    }

    /**
     * Perform any necessary rotate for landscape PDFs
     */
    public function rotateImage(): static
    {
        if ($this->settings->rotateDegrees) {
            $this->img->rotateImage(
                new ImagickPixel('white'),
                $this->settings->rotateDegrees,
            );
        }

        return $this;
    }

    public function processorType(): ImageProcessorOption
    {
        return ImageProcessorOption::Imagick;
    }
}
