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
final class LabelImageDownloadException extends AbstractPdfToZplException
{
    public static function forStatusCode(int $statusCode): self
    {
        return new self(sprintf('Failed to download image. Received status [%d].', $statusCode));
    }
}
