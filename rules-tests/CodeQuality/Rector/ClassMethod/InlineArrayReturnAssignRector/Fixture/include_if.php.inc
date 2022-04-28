<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector\Fixture;

final class IncludeIf
{
    public function run()
    {
        if (mt_rand(0, 1)) {
            $person = [];
            $person['name'] = 'Timmy';
            $person['surname'] = 'Back';

            return $person;
        }

        return null;
    }
}


?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector\Fixture;

final class IncludeIf
{
    public function run()
    {
        if (mt_rand(0, 1)) {
            return ['name' => 'Timmy', 'surname' => 'Back'];
        }

        return null;
    }
}


?>
