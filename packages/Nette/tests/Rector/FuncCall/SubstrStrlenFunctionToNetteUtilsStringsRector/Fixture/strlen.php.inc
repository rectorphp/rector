<?php

namespace Rector\Nette\Tests\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector\Fixture;

class Strlen
{
    public function run($value)
    {
        return strlen($value);
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector\Fixture;

class Strlen
{
    public function run($value)
    {
        return \Nette\Utils\Strings::length($value);
    }
}

?>
