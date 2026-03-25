<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Zpl\Settings\LabelDirection;

test('label direction is an enum', function (): void {
    expect(
        new ReflectionClass(LabelDirection::class)->isEnum(),
    )->toBeTrue();
});

test('label direction has the expected cases', function (): void {
    expect(enum_exists(LabelDirection::class))->toBeTrue()
        ->and(LabelDirection::Up->name)->toBe('Up')
        ->and(LabelDirection::Down->name)->toBe('Down')
        ->and(LabelDirection::Left->name)->toBe('Left')
        ->and(LabelDirection::Right->name)->toBe('Right');
});

test('label direction maps to the expected degrees', function (): void {
    expect(LabelDirection::Up->toDegree())->toBe(0)
        ->and(LabelDirection::Down->toDegree())->toBe(180)
        ->and(LabelDirection::Left->toDegree())->toBe(90)
        ->and(LabelDirection::Right->toDegree())->toBe(270);
});

test('default direction is up', function (): void {
    $default = LabelDirection::default();

    expect($default)->toBe(LabelDirection::Up)
        ->and($default->toDegree())->toBe(0);
});

test('all cases and degrees are distinct', function (): void {
    $degrees = [
        LabelDirection::Up->toDegree(),
        LabelDirection::Down->toDegree(),
        LabelDirection::Left->toDegree(),
        LabelDirection::Right->toDegree(),
    ];

    expect(array_unique($degrees))->toHaveCount(4)
        ->and(LabelDirection::Up)->not->toBe(LabelDirection::Down)
        ->and(LabelDirection::Up)->not->toBe(LabelDirection::Left)
        ->and(LabelDirection::Up)->not->toBe(LabelDirection::Right)
        ->and(LabelDirection::Down)->not->toBe(LabelDirection::Left)
        ->and(LabelDirection::Down)->not->toBe(LabelDirection::Right)
        ->and(LabelDirection::Left)->not->toBe(LabelDirection::Right);
});

test('label directions can be matched and are valid rotations', function (): void {
    $results = array_map(
        static fn (LabelDirection $direction): string => match ($direction) {
            LabelDirection::Up => 'up',
            LabelDirection::Down => 'down',
            LabelDirection::Left => 'left',
            LabelDirection::Right => 'right',
        },
        [LabelDirection::Up, LabelDirection::Down, LabelDirection::Left, LabelDirection::Right],
    );

    expect($results)->toBe(['up', 'down', 'left', 'right']);

    foreach ([LabelDirection::Up, LabelDirection::Down, LabelDirection::Left, LabelDirection::Right] as $direction) {
        expect($direction->toDegree())->toBeGreaterThanOrEqual(0)->toBeLessThan(360)->toBeIn([0, 90, 180, 270]);
    }
});
