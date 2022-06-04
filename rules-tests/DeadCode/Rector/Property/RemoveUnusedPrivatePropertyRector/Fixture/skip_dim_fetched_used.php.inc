<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

final class SkipDimFetchedUsed
{
    private $itemId;

    private $items;

    public function updateActive($itemId): void
    {
        $this->itemId = $itemId;
    }

    public function isActive()
    {
        return $this->items[$this->itemId] ?? 'yes';
    }
}
