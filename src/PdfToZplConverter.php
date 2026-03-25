<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Zpl;

use Cline\Zpl\Exceptions\AbstractPdfToZplException;
use Cline\Zpl\Exceptions\ImagickPdfAccessPolicyException;
use Cline\Zpl\Exceptions\UnreadableInputFileException;
use Cline\Zpl\Images\ImageProcessorInterface;
use Cline\Zpl\Images\ImagickProcessor;
use Cline\Zpl\Settings\ConverterSettings;
use Illuminate\Support\Collection;
use Imagick;
use ImagickException;
use ImagickPixel;

use function file_get_contents;
use function mb_strlen;

/**
 * Converts a PDF file into a list of ZPL commands * @author Brian Faust <brian@cline.sh>
 */
final class PdfToZplConverter implements ZplConverterServiceInterface
{
    /**
     * The error code for PDF permission issue
     */
    private const int IMAGICK_SECURITY_CODE = 499;

    public ConverterSettings $settings;

    private ImageToZplConverter $imageConverter;

    public function __construct(
        ?ConverterSettings $settings = null,
    ) {
        $this->settings = $settings ?? new ConverterSettings();
        $this->imageConverter = new ImageToZplConverter($this->settings);
    }

    public static function build(ConverterSettings $settings): self
    {
        return new self($settings);
    }

    /**
     * Extensions this converter is able to process
     */
    public static function canConvert(): array
    {
        return ['pdf'];
    }

    // Normal sized PDF: A4, Portrait (8.27 × 11.69 inch)
    // Desired sized PDF: prc 32k, Portrait (3.86 × 6.00 inch)

    /**
     * @return Collection<int, string>
     */
    public function pdfToZpls(string $pdfData): Collection
    {
        return $this->pdfToImages($pdfData)
            ->map(fn ($img) => $this->imageConverter->rawImageToZpl($img));
    }

    /**
     * @param  string                    $pdfData Raw PDF data as a string
     * @throws AbstractPdfToZplException
     * @return Collection<int, string>   A list of raw PNG data as a string
     */
    public function pdfToImages(string $pdfData): Collection
    {
        $img = new Imagick();
        $dpi = $this->settings->dpi;
        $img->setResolution($dpi, $dpi);
        $this->attemptReadBlob($img, $pdfData);

        $pages = $img->getNumberImages();
        $this->settings->log('Page count = '.$pages);
        $processor = new ImagickProcessor($img, $this->settings);
        $images = new Collection([]);

        for ($i = 0; $i < $pages; ++$i) {
            $this->settings->log('Working on page '.$i);
            $page = $this->processPage($img, $processor, pageIndex: $i);
            $images->push((string) $page);
        }
        $img->clear();

        return $images;
    }

    /**
     * Convert raw PDF data into an array of ZPL commands.
     * Each page of the PDF is 1 ZPL command.
     *
     * @return array<string>
     */
    public function convertFromBlob(string $pdfData): array
    {
        /** @var array<string> */
        return $this->pdfToZpls($pdfData)->toArray();
    }

    /**
     * Load a PDF file and convert it into an array of ZPL commands.
     * Each page of the PDF is 1 ZPL command.
     *
     * @throws AbstractPdfToZplException
     */
    public function convertFromFile(string $filepath): array
    {
        $rawData = file_get_contents($filepath);

        if (!$rawData) {
            throw UnreadableInputFileException::forPath($filepath);
        }
        $this->settings->log("File Size for {$filepath} is ".mb_strlen($rawData));

        return $this->convertFromBlob($rawData);
    }

    /**
     * Add a white background to the label
     */
    private function background(Imagick $img): Imagick
    {
        $background = new Imagick();
        $pixel = new ImagickPixel('white');
        $background->newImage($img->getImageWidth(), $img->getImageHeight(), $pixel);

        $background->setImageFormat(
            $img->getImageFormat(),
        );

        $background->compositeImage($img, Imagick::COMPOSITE_OVER, 0, 0);

        return $background;
    }

    private function processPage(Imagick $img, ImageProcessorInterface $imageProcessor, int $pageIndex): Imagick
    {
        $img->setIteratorIndex($pageIndex);

        $img->setImageCompressionQuality(100);

        $imageProcessor
            ->scaleImage()
            ->rotateImage();

        $img->setImageFormat('png');

        return $this->background($img);
    }

    private function attemptReadBlob(Imagick $img, string $pdfData): void
    {
        try {
            $img->readImageBlob($pdfData);
            $this->settings->log('Read blob...');
        } catch (ImagickException $exception) {
            if ($exception->getCode() === self::IMAGICK_SECURITY_CODE) {
                throw ImagickPdfAccessPolicyException::enablePdfSupport(
                    self::IMAGICK_SECURITY_CODE,
                    $exception,
                );
            }

            // No special handling
            throw $exception;
        }
    }
}
