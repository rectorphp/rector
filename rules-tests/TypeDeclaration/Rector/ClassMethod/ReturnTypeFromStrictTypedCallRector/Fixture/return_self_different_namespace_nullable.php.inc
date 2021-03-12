<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\ReturnSelfFromSourceNullable;

final class ReturnSelfDifferentNamespaceNullable
{
    public function run()
    {
        return
            array_map(function () {
                return ReturnSelfFromSourceNullable::fromEvent();
            }, ['event']);
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\ReturnSelfFromSourceNullable;

final class ReturnSelfDifferentNamespaceNullable
{
    public function run()
    {
        return
            array_map(function (): ?\Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\ReturnSelfFromSourceNullable {
                return ReturnSelfFromSourceNullable::fromEvent();
            }, ['event']);
    }
}

?>
