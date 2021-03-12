<?php

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

final class WithArrayType
{
    public function addSome(string $name)
    {
        $this->someProperty[$name] = true;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

final class WithArrayType
{
    /**
     * @var array<string, bool>
     */
    public array $someProperty;
    public function addSome(string $name)
    {
        $this->someProperty[$name] = true;
    }
}

?>
