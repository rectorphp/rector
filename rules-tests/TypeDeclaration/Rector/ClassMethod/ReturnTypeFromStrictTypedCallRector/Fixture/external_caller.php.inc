<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\SomeExternalCaller;

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

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\SomeExternalCaller;

final class ExternalCaller
{
    public function getData(SomeExternalCaller $someExternalCaller): string
    {
        return $someExternalCaller->getName();
    }
}

?>
