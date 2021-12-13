<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Class_\StringableForToStringRector\Fixture;

class ReturnVoid2
{
    public function __toString()
    {
        do_something();
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Class_\StringableForToStringRector\Fixture;

class ReturnVoid2 implements \Stringable
{
    public function __toString(): string
    {
        do_something();
        return '';
    }
}

?>
