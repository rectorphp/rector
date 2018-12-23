<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture;

class KnownStatic
{
    public function getItems()
    {
        return [];
    }

    public function getNumber()
    {
        return 5 + 10;
    }

    public function getName()
    {
        return 'hello';
    }

    public function getMoreItems()
    {
        if (true) {
            return [];
        }

        return [];
    }

    public function getCallable()
    {
        return function () {
        };
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture;

class KnownStatic
{
    public function getItems(): array
    {
        return [];
    }

    public function getNumber(): int
    {
        return 5 + 10;
    }

    public function getName(): string
    {
        return 'hello';
    }

    public function getMoreItems(): array
    {
        if (true) {
            return [];
        }

        return [];
    }

    public function getCallable(): callable
    {
        return function () {
        };
    }
}

?>
