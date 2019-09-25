<?php

namespace Rector\Php53\Tests\Rector\New_\NewReferenceToNewRector\Fixture;

class SomeClass
{
    public function run()
    {
        $value = &new stdClass;
    }
}

?>
-----
<?php

namespace Rector\Php53\Tests\Rector\New_\NewReferenceToNewRector\Fixture;

class SomeClass
{
    public function run()
    {
        $value = new stdClass;
    }
}

?>
