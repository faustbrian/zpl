<?php declare(strict_types=1);

/**
 * Copyright (C) Benjamin Faerber, Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Zpl\Exceptions;

/**
 * @author Benjamin Faerber
 * @author Brian Faust <brian@cline.sh>
 */
final class GdImageCreateException extends AbstractPdfToZplException
{
    public static function forScaledImage(): self
    {
        return new self('Failed to create GD image');
    }
}
