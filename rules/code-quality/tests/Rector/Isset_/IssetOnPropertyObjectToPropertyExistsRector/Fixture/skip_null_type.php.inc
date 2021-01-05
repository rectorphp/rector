<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

trait SkipNullType
{
    protected function sort(array $contents): array
    {
        $collator = new Collator('es');

        usort($contents, function ($a, $b) { return $this->sortItem($collator, $a, $b); });

        return $contents;
    }

    protected function sortItem(Collator $collator, object $a, object $b): int
    {
        $aFolder = isset($a->folder);
        $bFolder = isset($b->folder);

        if ($aFolder && $bFolder) {
            return $collator->compare($a->name, $b->name);
        }

        if ($aFolder) {
            return -1;
        }

        if ($bFolder) {
            return 1;
        }

        return $collator->compare($a->name, $b->name);
    }
}