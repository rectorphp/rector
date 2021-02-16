<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use function Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\getExternalBool;

final class ExternalFunctionCaller
{
    public function getData()
    {
        return getExternalBool();
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use function Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\getExternalBool;

final class ExternalFunctionCaller
{
    public function getData(): bool
    {
        return getExternalBool();
    }
}

?>
