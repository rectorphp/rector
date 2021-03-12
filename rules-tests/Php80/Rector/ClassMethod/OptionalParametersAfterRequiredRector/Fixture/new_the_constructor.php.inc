<?php

declare(strict_types=1);

namespace Rector\Php80\Tests\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Fixture;

final class NewTheConstructor
{
    public function __construct($optional = 1, $required)
    {
    }

    public function create()
    {
        return new self(1, 5);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Php80\Tests\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Fixture;

final class NewTheConstructor
{
    public function __construct($required, $optional = 1)
    {
    }

    public function create()
    {
        return new self(5, 1);
    }
}

?>
