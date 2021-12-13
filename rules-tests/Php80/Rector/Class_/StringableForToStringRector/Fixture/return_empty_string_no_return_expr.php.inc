<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Class_\StringableForToStringRector\Fixture;

class ReturnEmptyStringNoReturnExpr
{
    public function __toString()
    {
        if (rand(0, 1)) {
            return;
        }

        return 'test';
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Class_\StringableForToStringRector\Fixture;

class ReturnEmptyStringNoReturnExpr implements \Stringable
{
    public function __toString(): string
    {
        if (rand(0, 1)) {
            return '';
        }

        return 'test';
    }
}

?>
