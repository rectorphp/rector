<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

final class InferSetGet
{
    /**
     * @var string|null
     */
    private $name;

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function compareName(string $name): bool
    {
        return $name === $this->name;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\Property\DowngradeTypedPropertyRector\Fixture;

final class InferSetGet
{
    private ?string $name = null;

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function compareName(string $name): bool
    {
        return $name === $this->name;
    }
}

?>
