<?php

namespace Rector\Core\Tests\Issues\ScopeNotAvailable\Fixture;

class CallableThis
{
    public static function isAdmin(User $user): bool
    {
        return count(array_intersect($user->getRoles(), ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN'])) > 0;
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\ScopeNotAvailable\Fixture;

class CallableThis
{
    public static function isAdmin(User $user): bool
    {
        return array_intersect($user->getRoles(), ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN']) !== [];
    }
}

?>
