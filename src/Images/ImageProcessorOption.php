<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Zpl\Images;

use Cline\Zpl\Settings\ConverterSettings;
use Imagick;

/**
 * @author Brian Faust <brian@cline.sh>
 */
enum ImageProcessorOption
{
    /**
     * The faster and better processing option, it needs to be installed
     */
    case Gd;

    /**
     * The slower and worse processing option,
     * it is installed by default and is useful in environments where you cannot install extensions
     */
    case Imagick;

    public function processor(ConverterSettings $settings): ImageProcessorInterface
    {
        return match ($this) {
            self::Imagick => new ImagickProcessor(
                new Imagick(),
                $settings,
            ),
            self::Gd => new GdProcessor($settings),
        };
    }
}
