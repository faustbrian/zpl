<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Zpl\Exceptions;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class CompressedImageRowException extends AbstractPdfToZplException
{
    public static function failed(): self
    {
        return new self('Failed to compress image row');
    }
}
