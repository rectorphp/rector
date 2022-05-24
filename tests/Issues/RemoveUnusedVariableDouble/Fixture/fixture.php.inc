<?php

namespace Rector\Core\Tests\Issues\RemoveUnusedVariableDouble\Fixture;

final class Fixture
{
    public function run()
    {
        $user = null;
        $user = ['User' => ['name' => 'John']];
        $objects = [1, 2, 3];
        $groups = [];
        $defaultGroup = 'default';

        foreach ($objects as $aco) {
            $groups['aco'] = $aco;
        }

        return ['groups' => $groups, 'user' => $user];
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\RemoveUnusedVariableDouble\Fixture;

final class Fixture
{
    public function run()
    {
        $user = ['User' => ['name' => 'John']];
        $objects = [1, 2, 3];
        $groups = [];

        foreach ($objects as $aco) {
            $groups['aco'] = $aco;
        }

        return ['groups' => $groups, 'user' => $user];
    }
}

?>
