<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Support;

use Psr\Log\LoggerInterface;
use Tests\Exceptions\TestDataReadException;

use function array_map;
use function count;
use function file_get_contents;
use function mb_substr;
use function range;
use function similar_text;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class TestUtils
{
    public const int PERCENT_DIFFERENCE_TOLERANCE = 95;

    public function __construct(
        public readonly LoggerInterface $logger,
    ) {}

    public function testData(string $filename): string
    {
        return __DIR__.'/../Fixtures/Data/'.$filename;
    }

    public function testOutput(string $filename): string
    {
        return __DIR__.'/../Fixtures/Output/'.$filename;
    }

    public function fileGetContents(string $name): string
    {
        $data = file_get_contents($name);

        if ($data === false) {
            throw TestDataReadException::forPath($name);
        }

        return $data;
    }

    /**
     * @return list<string>
     */
    public function loadExpectedPages(string $name, int $pageCount): array
    {
        return array_map(
            fn (int $index): string => $this->fileGetContents($this->testOutput("{$name}_{$index}.zpl")),
            range(0, $pageCount - 1),
        );
    }

    /**
     * @param list<string> $pagesA
     * @param list<string> $pagesB
     */
    public function getPercentSimilar(array $pagesA, array $pagesB, string $context): float
    {
        $accumulator = 0.0;
        $comparisons = 0;

        for ($pageNumber = 0; $pageNumber < count($pagesA); ++$pageNumber) {
            $preview = static fn (string $string): string => mb_substr($string, 0, 10_000);
            similar_text($preview($pagesA[$pageNumber]), $preview($pagesB[$pageNumber]), $percent);
            $this->logger->info("Texts are {$percent}% similar ({$context})");
            $accumulator += $percent;
            ++$comparisons;
        }

        $average = $accumulator / $comparisons;
        $this->logger->info("Texts are {$average}% similar ({$context})");

        return $average;
    }

    /**
     * @param list<string> $pages
     */
    public function percentSimilarToExpected(array $pages, string $expectedFilename, string $context): float
    {
        $expectedPages = $this->loadExpectedPages($expectedFilename, count($pages));

        return $this->getPercentSimilar($pages, $expectedPages, $context);
    }
}
