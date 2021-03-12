<?php

namespace Rector\Tests\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector\Fixture;

use Rector\Tests\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector\Source\LocalFormEvents;

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

namespace Rector\Tests\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector\Fixture;

use Rector\Tests\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector\Source\LocalFormEvents;

class SomeClass
{
    public function subscribe()
    {
        return [
            LocalFormEvents::PRE_SUBMIT,
            LocalFormEvents::PRE_SUBMIT,
            \Rector\Tests\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector\Source\DifferentClass::NEW_CONSTANT,
        ];
    }
}

?>
