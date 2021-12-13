<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Class_\StringableForToStringRector\Fixture;

class ReturnVoid
{
    public function __toString()
    {
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Class_\StringableForToStringRector\Fixture;

class ReturnVoid implements \Stringable
{
    public function __toString(): string
    {
        return '';
    }
}

?>
