<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Zpl;

use Cline\Zpl\Exceptions\AbstractPdfToZplException;
use Cline\Zpl\Settings\ConverterSettings;

/**
 * A converter able to convert certain file types into ZPL * @author Brian Faust <brian@cline.sh>
 */
interface ZplConverterServiceInterface
{
    /**
     * Get a list of extensions that this converter can convert
     * @return array<string>
     */
    public static function canConvert(): array;

    /**
     * Create a new converter service.
     * This is preferred over the constructor as it can be verified via this interface.
     */
    public static function build(ConverterSettings $settings): self;

    /**
     * Read and convert a file into a list of ZPL commands (1 per page)
     * @throws AbstractPdfToZplException
     * @return array<string>
     */
    public function convertFromFile(string $filepath): array;

    /**
     * Convert a raw blob of binary data into a list of ZPL commands (1 per page)
     * @throws AbstractPdfToZplException
     * @return array<string>
     */
    public function convertFromBlob(string $rawData): array;
}
