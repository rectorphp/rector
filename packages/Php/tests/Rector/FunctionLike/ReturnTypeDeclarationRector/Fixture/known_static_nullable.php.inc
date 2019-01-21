<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class KnownStaticNullable
{
    public function getMoreItems()
    {
        if (true) {
            return null;
        }

        return [];
    }

    public function getFloating()
    {
        if (true) {
            return 5.2;
        }

        $value = 5.3;
        return $value;
    }

    public function getStringNull()
    {
        /** @var string|null $value */
        $value = 'hi';

        return $value;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class KnownStaticNullable
{
    public function getMoreItems(): ?array
    {
        if (true) {
            return null;
        }

        return [];
    }

    public function getFloating(): float
    {
        if (true) {
            return 5.2;
        }

        $value = 5.3;
        return $value;
    }

    public function getStringNull(): ?string
    {
        /** @var string|null $value */
        $value = 'hi';

        return $value;
    }
}

?>
