<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Zpl\Settings;

/**
 * @author Brian Faust <brian@cline.sh>
 */
enum ImageScale
{
    /**
     * Scale the Image to fill all available space
     * (does not respect aspect ratio)
     */
    case Fill;

    /**
     * Scale the Image to fill the most available space while respecting aspect ratio
     */
    case Cover;

    /**
     * Do not scale the image in anyway (could cause the image to not fit on the label)
     */
    case None;

    public function shouldResize(): bool
    {
        return $this === self::Fill || $this === self::Cover;
    }

    public function isBestFit(): bool
    {
        return $this === self::Cover;
    }
}
