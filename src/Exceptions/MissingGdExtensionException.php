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

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class MissingGdExtensionException extends AbstractPdfToZplException implements ProvidesSolution
{
    public static function installOrUseImagick(): self
    {
        return new self('You must install the GD image library or change imageProcessorOption to ImageProcessOption::Imagick');
    }

    public function getSolution(): Solution
    {
        $solution = new BaseSolution('Install the GD extension or switch processors');
        $solution->setSolutionDescription("Install PHP's GD extension, or configure `imageProcessorOption` to `ImageProcessorOption::Imagick`.");
        $solution->setDocumentationLinks([
            'PHP GD installation' => 'https://www.php.net/manual/en/image.installation.php',
        ]);

        return $solution;
    }
}
