<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

class ReturnSelfNullable {
    public static function fromEvent() : ?self
    {
        if (rand(0, 1)) {
            new self;
        }

        return null;
    }
}

final class UseReturnSelfNullable
{
    public function run()
    {
        return
            array_map(function () {
                return ReturnSelfNullable::fromEvent();
            }, ['event']);
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

class ReturnSelfNullable {
    public static function fromEvent() : ?self
    {
        if (rand(0, 1)) {
            new self;
        }

        return null;
    }
}

final class UseReturnSelfNullable
{
    public function run()
    {
        return
            array_map(function (): ?\Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture\ReturnSelfNullable {
                return ReturnSelfNullable::fromEvent();
            }, ['event']);
    }
}

?>
