<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Zpl\Settings;

/**
 * @author Brian Faust <brian@cline.sh>
 */
enum LabelDirection
{
    case Up;
    case Down;
    case Left;
    case Right;

    public static function default(): self
    {
        return self::Up;
    }

    public function toDegree(): int
    {
        return match ($this) {
            self::Up => 0,
            self::Down => 180,
            self::Left => 90,
            self::Right => 270,
        };
    }
}
