<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class InMethodCall
{
    public function run(): void
    {
        $string = $this->getString('foo',);
        // ...
    }
    public function getString(...$params): string
    {
        return implode(', ', $params);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class InMethodCall
{
    public function run(): void
    {
        $string = $this->getString('foo');
        // ...
    }
    public function getString(...$params): string
    {
        return implode(', ', $params);
    }
}

?>
