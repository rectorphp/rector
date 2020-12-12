<?php

namespace Rector\Restoration\Tests\Rector\ClassConstFetch\MissingClassConstantReferenceToStringRector\Fixture;

use NonExistingClass;

class Fixture
{
    public function run()
    {
        return NonExistingClass::class;
    }
}

?>
-----
<?php

namespace Rector\Restoration\Tests\Rector\ClassConstFetch\MissingClassConstantReferenceToStringRector\Fixture;

use NonExistingClass;

class Fixture
{
    public function run()
    {
        return 'NonExistingClass';
    }
}

?>
