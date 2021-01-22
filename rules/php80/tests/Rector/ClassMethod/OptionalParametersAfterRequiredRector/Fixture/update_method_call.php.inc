<?php

declare(strict_types=1);

namespace Rector\Php80\Tests\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Fixture;

final class UpdateMethodCall
{
    public function run($optional = 1, $required)
    {
    }

    public function process()
    {
        $this->run(1, 5);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Php80\Tests\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Fixture;

final class UpdateMethodCall
{
    public function run($required, $optional = 1)
    {
    }

    public function process()
    {
        $this->run(5, 1);
    }
}

?>
