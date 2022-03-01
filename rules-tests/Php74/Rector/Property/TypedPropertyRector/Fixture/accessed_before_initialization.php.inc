<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class AccessedBeforeInitialization
{
    /**
     * @var string
     */
    private $name;

    private function generateName(): void
    {
        $this->name = bin2hex(random_bytes(10));
    }

    public function getName()
    {
        if ($this->name === null) {
            $this->generateName();
        }

        return $this->name;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class AccessedBeforeInitialization
{
    private ?string $name = null;

    private function generateName(): void
    {
        $this->name = bin2hex(random_bytes(10));
    }

    public function getName()
    {
        if ($this->name === null) {
            $this->generateName();
        }

        return $this->name;
    }
}

?>
