<?php

namespace Rector\Tests\Nette\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector\Fixture;

class SomeClass
{
    public function run()
    {
        return substr($value, 0, 3);
    }
}

?>
-----
<?php

namespace Rector\Tests\Nette\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector\Fixture;

class SomeClass
{
    public function run()
    {
        return \Nette\Utils\Strings::substring($value, 0, 3);
    }
}

?>
