<?php

use function Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Function_\count;

class ImportFunctionWithoutNamespace
{
    public function run()
    {
        return \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Function_\count([1]);
    }
}

?>
-----
<?php

use function Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Function_\count;

class ImportFunctionWithoutNamespace
{
    public function run()
    {
        return count([1]);
    }
}

?>
