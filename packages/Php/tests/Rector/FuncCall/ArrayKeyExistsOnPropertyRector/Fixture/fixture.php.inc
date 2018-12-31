<?php

namespace Rector\Php\Tests\Rector\FuncCall\ArrayKeyExistsOnPropertyRector\Fixture;

class SomeClass {
     public $value;
}
$someClass = new SomeClass;

array_key_exists('value', $someClass);

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\ArrayKeyExistsOnPropertyRector\Fixture;

class SomeClass {
     public $value;
}
$someClass = new SomeClass;

property_exists($someClass, 'value');

?>
