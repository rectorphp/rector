<?php

namespace SomeNamespace;

use Rector\Tests\Rector\Constant\RenameClassConstantRector\Source\LocalFormEvents;

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

namespace SomeNamespace;

use Rector\Tests\Rector\Constant\RenameClassConstantRector\Source\LocalFormEvents;

class SomeClass
{
    public function subscribe()
    {
        return [
            LocalFormEvents::PRE_SUBMIT,
            LocalFormEvents::PRE_SUBMIT,
            \Rector\Tests\Rector\Constant\RenameClassConstantRector\Source\DifferentClass::NEW_CONSTANT,
        ];
    }
}

?>
