<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Fixture;

final class UsesWithLess
{
    public function blah($optional = 1, $required, $optional_2 = 2)
    {
    }

    public function process()
    {
        $this->blah(1, 5);
        $this->blah(1, 5, 2);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Fixture;

final class UsesWithLess
{
    public function blah($required, $optional = 1, $optional_2 = 2)
    {
    }

    public function process()
    {
        $this->blah(5, 1);
        $this->blah(5, 1, 2);
    }
}

?>
