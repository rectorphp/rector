<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Source\RolesProvider;

class SkipNestedForeachMethod
{
    /**
     * @var RolesProvider[]
     */
    private $rolesProviders = [];

    public function run(): array
    {
        $roles = [];

        foreach ($this->rolesProviders as $rolesProvider) {
            foreach ($rolesProvider->getRoles() as $role) {
                $roles[] = $role;
            }
        }

        return $roles;
    }
}
