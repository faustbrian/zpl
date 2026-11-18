<?php declare(strict_types=1);

/**
 * Copyright (C) Benjamin Faerber, Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Zpl\Exceptions;

use function sprintf;

/**
 * @author Benjamin Faerber
 * @author Brian Faust <brian@cline.sh>
 */
final class UnreadableInputFileException extends AbstractPdfToZplException
{
    public static function forPath(string $filepath): self
    {
        return new self(sprintf('Unable to read input file [%s].', $filepath));
    }
}
