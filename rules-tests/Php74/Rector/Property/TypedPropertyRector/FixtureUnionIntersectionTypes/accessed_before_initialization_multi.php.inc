<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

final class AccessedBeforeInitializationMulti
{
    /**
     * @var string|\stdClass
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

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

final class AccessedBeforeInitializationMulti
{
    private string|\stdClass|null $name = null;

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
