<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector\Fixture;

final class EmptyArray
{
    public function run()
    {
        $person = [];

        $person[] = 'Timmy';
        $person[] = 'Back';

        return $person;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector\Fixture;

final class EmptyArray
{
    public function run()
    {
        return ['Timmy', 'Back'];
    }
}

?>
