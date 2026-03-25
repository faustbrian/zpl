<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Zpl\Exceptions;

use function sprintf;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class UnsupportedConverterException extends AbstractPdfToZplException
{
    public static function forExtension(string $extension): self
    {
        return new self(sprintf('No converter for %s files!', $extension));
    }
}
