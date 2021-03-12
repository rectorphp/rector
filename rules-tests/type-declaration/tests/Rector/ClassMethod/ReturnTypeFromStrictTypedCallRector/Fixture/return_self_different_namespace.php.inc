<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\ReturnSelfFromSource;

final class ReturnSelfDifferentNamespace
{
    public function run()
    {
        return
            array_map(function () {
                return ReturnSelfFromSource::fromEvent();
            }, ['event']);
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\ReturnSelfFromSource;

final class ReturnSelfDifferentNamespace
{
    public function run()
    {
        return
            array_map(function (): \Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\ReturnSelfFromSource {
                return ReturnSelfFromSource::fromEvent();
            }, ['event']);
    }
}

?>
