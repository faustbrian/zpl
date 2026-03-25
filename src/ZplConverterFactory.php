<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Zpl;

use Cline\Zpl\Exceptions\AbstractPdfToZplException;
use Cline\Zpl\Exceptions\UnsupportedConverterException;
use Cline\Zpl\Settings\ConverterSettings;

use const PATHINFO_EXTENSION;

use function in_array;
use function pathinfo;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class ZplConverterFactory
{
    public const array CONVERTER_SERVICES = [
        PdfToZplConverter::class,
        ImageToZplConverter::class,
    ];

    /**
     * @throws AbstractPdfToZplException
     */
    public static function converterFromFile(string $filepath, ?ConverterSettings $settings = null): ZplConverterServiceInterface
    {
        $ext = pathinfo($filepath, PATHINFO_EXTENSION);
        $settings ??= new ConverterSettings();
        $settings->log("Converting {$filepath} ({$ext})");

        foreach (self::CONVERTER_SERVICES as $service) {
            if (in_array($ext, $service::canConvert(), true)) {
                $settings->log("Using {$service} converter");

                return $service::build($settings);
            }
        }

        throw UnsupportedConverterException::forExtension($ext);
    }
}
