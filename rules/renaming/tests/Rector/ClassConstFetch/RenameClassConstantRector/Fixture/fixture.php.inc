<?php

namespace Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstantRector\Fixture;

use Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstantRector\Source\LocalFormEvents;

class SomeClass
{
    public function subscribe()
    {
        return [
            LocalFormEvents::PRE_BIND,
            LocalFormEvents::PRE_Bind,
            LocalFormEvents::OLD_CONSTANT,
        ];
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstantRector\Fixture;

use Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstantRector\Source\LocalFormEvents;

class SomeClass
{
    public function subscribe()
    {
        return [
            LocalFormEvents::PRE_SUBMIT,
            LocalFormEvents::PRE_SUBMIT,
            \Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstantRector\Source\DifferentClass::NEW_CONSTANT,
        ];
    }
}

?>
