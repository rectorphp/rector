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
        if ($countMatchAll !== 2) {
            return false;
        }
        if ($matchAll[0][1] !== $expectedNamespace) {
            return false;
        }
        return $matchAll[1][1] === $expectedNamespace;
    }
}
