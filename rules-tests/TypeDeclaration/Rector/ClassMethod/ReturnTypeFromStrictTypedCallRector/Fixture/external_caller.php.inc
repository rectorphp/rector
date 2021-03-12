<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\SomeExternalCaller;

final class ExternalCaller
{
    public function getData(SomeExternalCaller $someExternalCaller)
    {
        return $someExternalCaller->getName();
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\SomeExternalCaller;

final class ExternalCaller
{
    public function getData(SomeExternalCaller $someExternalCaller): string
    {
        return $someExternalCaller->getName();
    }
}

?>
