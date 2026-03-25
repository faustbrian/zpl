<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Cline\CodingStandard\Rector\Factory;
use Rector\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector;
use Rector\CodeQuality\Rector\FuncCall\SortCallLikeNamedArgsRector;
use Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector;
use Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\ClassMethod\NewInInitializerRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector;
use RectorLaravel\Rector\FuncCall\ThrowIfAndThrowUnlessExceptionsToUseClassStringRector;
use RectorLaravel\Rector\If_\ThrowIfRector;
use RectorLaravel\Rector\MethodCall\ConvertEnumerableToArrayToAllRector;

return Factory::create(
    paths: [__DIR__.'/src', __DIR__.'/tests'],
    skip: [
        RemoveUnreachableStatementRector::class => [__DIR__.'/tests'],
        NewlineBetweenClassLikeStmtsRector::class,
        ThrowIfAndThrowUnlessExceptionsToUseClassStringRector::class,
        ThrowIfRector::class,
        ForRepeatedCountToOwnVariableRector::class,
        EncapsedStringsToSprintfRector::class,
        ReadOnlyClassRector::class,
        SortCallLikeNamedArgsRector::class,
        NewInInitializerRector::class,
        AddArrowFunctionReturnTypeRector::class,
        ReadOnlyPropertyRector::class,
        ConvertEnumerableToArrayToAllRector::class,
        CatchExceptionNameMatchingTypeRector::class,
        ParamTypeByMethodCallTypeRector::class,
        ClassPropertyAssignToConstructorPromotionRector::class,
        SimplifyArraySearchRector::class,
        SwitchNegatedTernaryRector::class,
        NewlineAfterStatementRector::class,
    ],
);
