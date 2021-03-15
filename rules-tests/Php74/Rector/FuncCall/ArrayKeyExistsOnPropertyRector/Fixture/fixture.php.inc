<?php

namespace Rector\Tests\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector\Fixture;

class Fixture
{
     public $value;
}
$someClass = new Fixture;

array_key_exists('value', $someClass);

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector\Fixture;

class Fixture
{
     public $value;
}
$someClass = new Fixture;

property_exists($someClass, 'value');

?>
