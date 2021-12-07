<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\Fixture;

final class ReturnThrowArrayDimFetch
{
    public function run($name)
    {
        return $variable[$name] ?? throw new \InvalidArgumentException();
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\Fixture;

final class ReturnThrowArrayDimFetch
{
    public function run($name)
    {
        if (!isset($variable[$name])) {
            throw new \InvalidArgumentException();
        }
        return $variable[$name];
    }
}

?>
