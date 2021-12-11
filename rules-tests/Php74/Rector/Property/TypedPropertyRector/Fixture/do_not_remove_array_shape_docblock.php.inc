<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DoNotRemoveArrayShapeDocblock
{
    const FIRST = 'a';
    const SECOND = 'b';

    /**
     * @var array{a: \stdClass[], b: \DateTime[]}
     */
    private $property = [
        self::FIRST => [],
        self::SECOND => [],
    ];

    public function getProperty()
    {
        if ($this->property['a'] === [] && $this->property['b'] === []) {
            $this->property['a'] = [new stdClass()];
            $this->property['b'] = [new \DateTime()];
        }

        return $this->property;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DoNotRemoveArrayShapeDocblock
{
    const FIRST = 'a';
    const SECOND = 'b';

    /**
     * @var array{a: \stdClass[], b: \DateTime[]}
     */
    private array $property = [
        self::FIRST => [],
        self::SECOND => [],
    ];

    public function getProperty()
    {
        if ($this->property['a'] === [] && $this->property['b'] === []) {
            $this->property['a'] = [new stdClass()];
            $this->property['b'] = [new \DateTime()];
        }

        return $this->property;
    }
}

?>
