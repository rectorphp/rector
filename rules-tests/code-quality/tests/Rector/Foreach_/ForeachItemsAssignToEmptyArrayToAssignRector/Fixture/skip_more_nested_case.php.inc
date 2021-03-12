<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

final class SkipMoreNestedCase
{
    /**
     * List of checkers with the same functionality.
     * If found, only the first one is used.
     *
     * @var string[][]
     */
    private static $duplicatedCheckerGroups = [
        [
            'SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff',
            'Symplify\CodingStandard\Sniffs\Namespaces\ClassNamesWithoutPreSlashSniff',
        ],
    ];

    /**
     * @param string[] $checkers
     * @return string[]
     */
    public function resolveCheckersToRemove(array $checkers): array
    {
        $checkersToRemove = [];
        foreach (self::$duplicatedCheckerGroups as $matchingCheckerGroup) {
            if ($matchingCheckerGroup === []) {
                continue;
            }

            foreach ($matchingCheckerGroup as $checkerToRemove) {
                $checkersToRemove[] = $checkerToRemove;
            }
        }
    }
}
