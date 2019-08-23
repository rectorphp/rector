<?php

namespace Rector\Architecture\Tests\Rector\Class_\ConvertSetterValueObjectToConstructorValueObjectRector\Fixture;

class SomeValueObject
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $age;

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function setAge(int $age)
    {
        $this->age = age;
    }
}

class SomeClass
{
    public function run()
    {
        $someValueObject = new SomeValueObject();
        $someValueObject->setName('Tom');
        $someValueObject->setAge(50);
    }
}

?>
-----
<?php

namespace Rector\Architecture\Tests\Rector\Class_\ConvertSetterValueObjectToConstructorValueObjectRector\Fixture;

class SomeValueObject
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $age;

    public function __construct(string $name, int $age)
    {
        $this->name = $name;
        $this->age = age;
    }
}

class SomeClass
{
    public function run()
    {
        $someValueObject = new SomeValueObject('Tom', 50);
    }
}

?>
