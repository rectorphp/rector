<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\SomeExternalStaticCaller;

final class ExternalStaticCaller
{
    public function getData()
    {
        return SomeExternalStaticCaller::getNumbers();
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\SomeExternalStaticCaller;

final class ExternalStaticCaller
{
    public function getData(): int
    {
        return SomeExternalStaticCaller::getNumbers();
    }
}

?>
