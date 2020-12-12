<?php

namespace Rector\Php74\Tests\Rector\FuncCall\ArrayKeyExistsOnPropertyRector\Fixture;

class Fixture
{
     public $value;
}
$someClass = new SomeClass;

array_key_exists('value', $someClass);

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\FuncCall\ArrayKeyExistsOnPropertyRector\Fixture;

class Fixture
{
     public $value;
}
$someClass = new SomeClass;

property_exists($someClass, 'value');

?>
