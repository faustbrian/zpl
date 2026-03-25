<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Zpl\Exceptions;

use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Throwable;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class ImagickPdfAccessPolicyException extends AbstractPdfToZplException implements ProvidesSolution
{
    public static function enablePdfSupport(int $code, Throwable $previous): self
    {
        return new self(
            'You need to enable PDF reading and writing in your Imagick settings (see docs for more details)',
            code: $code,
            previous: $previous,
        );
    }

    public function getSolution(): Solution
    {
        $solution = new BaseSolution('Allow PDF access in the ImageMagick policy');
        $solution->setSolutionDescription('Update your ImageMagick policy configuration to allow PDF read and write operations, then retry the conversion.');
        $solution->setDocumentationLinks([
            'ImageMagick security policy' => 'https://imagemagick.org/script/security-policy.php',
        ]);

        return $solution;
    }
}
