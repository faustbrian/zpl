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
final class ImagickPdfFormatMissingException extends AbstractPdfToZplException implements ProvidesSolution
{
    public static function installGhostscript(): self
    {
        return new self('Format PDF not allowed for Imagick (try installing ghostscript: sudo apt-get install -y ghostscript)');
    }

    public function getSolution(): Solution
    {
        $solution = new BaseSolution('Install Ghostscript support for PDF decoding');
        $solution->setSolutionDescription('Install Ghostscript so Imagick can read PDF files, then verify that `Imagick::queryFormats()` includes `PDF`.');
        $solution->setDocumentationLinks([
            'Imagick supported formats' => 'https://www.php.net/manual/en/imagick.queryformats.php',
            'Ghostscript' => 'https://ghostscript.com/',
        ]);

        return $solution;
    }
}
