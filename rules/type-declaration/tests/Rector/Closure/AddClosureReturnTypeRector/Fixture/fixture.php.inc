<?php

namespace Rector\TypeDeclaration\Tests\Rector\Closure\AddClosureReturnTypeRector\Fixture;

class Fixture
{
    public function run($meetups)
    {
        return array_filter($meetups, function ($meetup) {
            return is_object($meetup);
        });
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Closure\AddClosureReturnTypeRector\Fixture;

class Fixture
{
    public function run($meetups)
    {
        return array_filter($meetups, function ($meetup): bool {
            return is_object($meetup);
        });
    }
}

?>
