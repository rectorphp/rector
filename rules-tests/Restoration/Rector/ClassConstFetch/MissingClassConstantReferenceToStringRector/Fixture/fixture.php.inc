<?php

namespace Rector\Tests\Restoration\Rector\ClassConstFetch\MissingClassConstantReferenceToStringRector\Fixture;

use NonExistingClass;

class SomeClass
{
    public function run()
    {
        return NonExistingClass::class;
    }
}

?>
-----
<?php

namespace Rector\Tests\Restoration\Rector\ClassConstFetch\MissingClassConstantReferenceToStringRector\Fixture;

use NonExistingClass;

class SomeClass
{
    public function run()
    {
        return 'NonExistingClass';
    }
}

?>
