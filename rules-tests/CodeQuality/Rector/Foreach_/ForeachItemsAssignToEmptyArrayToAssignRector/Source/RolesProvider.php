<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Source;

final class RolesProvider
{
    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return ['play', 'role'];
    }
}
