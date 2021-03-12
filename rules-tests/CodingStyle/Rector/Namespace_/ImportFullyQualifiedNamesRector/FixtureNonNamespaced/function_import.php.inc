<?php

use function Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Function_\count;

class ImportFunctionWithoutNamespace
{
    public function run()
    {
        return \Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Function_\count([1]);
    }
}

?>
-----
<?php

use function Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Function_\count;

class ImportFunctionWithoutNamespace
{
    public function run()
    {
        return count([1]);
    }
}

?>
