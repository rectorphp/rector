<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\ReturnSelfFromSourceNullable;

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

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\ReturnSelfFromSourceNullable;

final class ReturnSelfDifferentNamespaceNullable
{
    public function run()
    {
        return
            array_map(function (): ?\Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\ReturnSelfFromSourceNullable {
                return ReturnSelfFromSourceNullable::fromEvent();
            }, ['event']);
    }
}

?>
