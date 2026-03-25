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
final class MissingImagickExtensionException extends AbstractPdfToZplException implements ProvidesSolution
{
    public static function install(): self
    {
        return new self('You must install the Imagick image library');
    }

    public function getSolution(): Solution
    {
        $solution = new BaseSolution('Install the Imagick extension');
        $solution->setSolutionDescription("Install PHP's Imagick extension, then restart PHP so the extension is available at runtime.");
        $solution->setDocumentationLinks([
            'Imagick installation' => 'https://www.php.net/manual/en/imagick.setup.php',
        ]);

        return $solution;
    }
}
