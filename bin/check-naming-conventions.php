<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Zpl\Bin;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

use const STDERR;
use const T_CLASS;
use const T_INTERFACE;
use const T_NAME_QUALIFIED;
use const T_NAMESPACE;
use const T_NS_SEPARATOR;
use const T_STRING;
use const T_TRAIT;

use function array_unique;
use function count;
use function file_get_contents;
use function fwrite;
use function in_array;
use function is_array;
use function is_string;
use function sprintf;
use function str_ends_with;
use function str_starts_with;
use function token_get_all;

require __DIR__.'/../vendor/autoload.php';

$violations = [];

foreach (sourceClassNames() as $className) {
    $reflectionClass = new ReflectionClass($className);
    $shortName = $reflectionClass->getShortName();

    if ($reflectionClass->isInterface() && !str_ends_with($shortName, 'Interface')) {
        $violations[] = sprintf('Interface [%s] must end with "Interface".', $className);
    }

    if (
        $reflectionClass->isAbstract()
        && !$reflectionClass->isInterface()
        && !str_starts_with($shortName, 'Abstract')
    ) {
        $violations[] = sprintf('Abstract class [%s] must start with "Abstract".', $className);
    }

    if (!$reflectionClass->isTrait() || str_ends_with($shortName, 'Trait')) {
        continue;
    }

    $violations[] = sprintf('Trait [%s] must end with "Trait".', $className);
}

if ($violations === []) {
    exit(0);
}

fwrite(STDERR, "Naming convention violations detected:\n");

foreach (array_unique($violations) as $violation) {
    fwrite(STDERR, sprintf("- %s\n", $violation));
}

exit(1);

/**
 * @return list<class-string>
 */
function sourceClassNames(): array
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__.'/../src'),
    );

    $classNames = [];

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }

        if ($fileInfo->getExtension() !== 'php') {
            continue;
        }

        $contents = file_get_contents($fileInfo->getPathname());

        if (!is_string($contents)) {
            continue;
        }

        $namespace = extractTokenName($contents, T_NAMESPACE);
        $className = extractTokenName($contents, T_CLASS)
            ?? extractTokenName($contents, T_INTERFACE)
            ?? extractTokenName($contents, T_TRAIT);

        if ($namespace === null || $className === null) {
            continue;
        }

        $classNames[] = $namespace.'\\'.$className;
    }

    return $classNames;
}

function extractTokenName(string $contents, int $targetToken): ?string
{
    $tokens = token_get_all($contents);
    $capturingNamespace = false;
    $namespace = '';

    foreach ($tokens as $index => $token) {
        if (!is_array($token)) {
            if ($capturingNamespace && ($token === ';' || $token === '{')) {
                $capturingNamespace = false;
            }

            continue;
        }

        [$id, $text] = $token;

        if ($id === T_NAMESPACE) {
            $capturingNamespace = true;
            $namespace = '';

            continue;
        }

        if ($capturingNamespace && in_array($id, [T_STRING, T_NAME_QUALIFIED, T_NS_SEPARATOR], true)) {
            $namespace .= $text;

            continue;
        }

        if ($id !== $targetToken) {
            continue;
        }

        for ($offset = $index + 1, $count = count($tokens); $offset < $count; ++$offset) {
            $candidate = $tokens[$offset];

            if (!is_array($candidate)) {
                continue;
            }

            if ($candidate[0] === T_STRING) {
                return $targetToken === T_NAMESPACE ? $namespace : $candidate[1];
            }
        }
    }

    return $targetToken === T_NAMESPACE && $namespace !== '' ? $namespace : null;
}
