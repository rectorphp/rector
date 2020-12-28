<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator\Naming;

final class NamespaceMatcher
{
    /**
     * @param array<int, array<int, string>> $matchAll
     */
    public function isFoundCorrectNamespace(array $matchAll, string $expectedNamespace): bool
    {
        if ($matchAll === []) {
            return true;
        }

        $countMatchAll = count($matchAll);
        if ($countMatchAll === 1 && $matchAll[0][1] === $expectedNamespace) {
            return true;
        }

        return $countMatchAll === 2 && $matchAll[0][1] === $expectedNamespace && $matchAll[1][1] === $expectedNamespace;
    }
}
