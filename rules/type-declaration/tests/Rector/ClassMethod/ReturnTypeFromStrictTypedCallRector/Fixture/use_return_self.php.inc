<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

class ReturnSelf {
    public static function fromEvent() : self
    {
        return new self;
    }
}

final class UseReturnSelf
{
    public function run()
    {
        return
            array_map(function () {
                return ReturnSelf::fromEvent();
            }, ['event']);
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

class ReturnSelf {
    public static function fromEvent() : self
    {
        return new self;
    }
}

final class UseReturnSelf
{
    public function run()
    {
        return
            array_map(function (): \Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture\ReturnSelf {
                return ReturnSelf::fromEvent();
            }, ['event']);
    }
}

?>
