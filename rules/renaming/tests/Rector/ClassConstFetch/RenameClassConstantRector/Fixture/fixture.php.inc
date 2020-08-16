<?php

namespace Rector\Renaming\Tests\Rector\ClassConstFetchRenameClassConstantRector\Fixture;

use Rector\Renaming\Tests\Rector\ClassConstFetchRenameClassConstantRector\Source\LocalFormEvents;

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

namespace Rector\Renaming\Tests\Rector\ClassConstFetchRenameClassConstantRector\Fixture;

use Rector\Renaming\Tests\Rector\ClassConstFetchRenameClassConstantRector\Source\LocalFormEvents;

class SomeClass
{
    public function subscribe()
    {
        return [
            LocalFormEvents::PRE_SUBMIT,
            LocalFormEvents::PRE_SUBMIT,
            \Rector\Renaming\Tests\Rector\ClassConstFetchRenameClassConstantRector\Source\DifferentClass::NEW_CONSTANT,
        ];
    }
}

?>
