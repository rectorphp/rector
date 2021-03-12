<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\SomeExternalStaticCaller;

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

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\SomeExternalStaticCaller;

final class ExternalStaticCaller
{
    public function getData(): int
    {
        return SomeExternalStaticCaller::getNumbers();
    }
}

?>
