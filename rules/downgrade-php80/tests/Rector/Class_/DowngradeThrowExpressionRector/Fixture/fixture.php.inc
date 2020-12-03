<?php

namespace Rector\DowngradePhp80\Tests\Rector\Class_\DowngradeThrowExpressionRector\Fixture;

class SomeClass
{
    public function __construct($nullableValue)
    {
        return $nullableValue ?? throw new InvalidArgumentException()
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\Class_\DowngradeThrowExpressionRector\Fixture;

class SomeClass
{
    public function __construct($nullableValue)
    {
        if ($nullableValue !== null) {
            return $nullableValue;
        }

        throw new InvalidArgumentException()
    }
}

?>
