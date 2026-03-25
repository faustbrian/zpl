<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Zpl\Exceptions;

use Exception;
use Stringable;
use Throwable;

/**
 * A custom exception to let you know this is a library related error * @author Brian Faust <brian@cline.sh>
 */
abstract class AbstractPdfToZplException extends Exception implements Stringable, ZplExceptionInterface
{
    /**
     * @param null|array<bool|int|string> $context
     */
    public function __construct(
        string $message,
        int $code = 0,
        ?Throwable $previous = null,
        /** @var null|array<bool|int|string> */
        public readonly ?array $context = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString(): string
    {
        return static::class.": {$this->message}";
    }
}
