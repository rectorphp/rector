<?php

namespace Rector\Tests\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector\Fixture;

use Doctrine\ORM\Mapping\Column;

class SomeClass
{
    #[Column(type: "string")]
    public $name;
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector\Fixture;

use Doctrine\ORM\Mapping\Column;

class SomeClass
{
    #[Column(type: \Doctrine\DBAL\Types\Types::STRING)]
    public $name;
}

?>
