<?php

namespace Rector\Tests\Rector\Property\PropertyNameReplacerRector\Fixture;

use Rector\Tests\Rector\Property\PropertyNameReplacerRector\Source\ClassWithProperties;

$someService = new ClassWithProperties();
$someService->oldProperty = 5;
$someService->anotherOLDProperty = 5;

?>
-----
<?php

namespace Rector\Tests\Rector\Property\PropertyNameReplacerRector\Fixture;

use Rector\Tests\Rector\Property\PropertyNameReplacerRector\Source\ClassWithProperties;

$someService = new ClassWithProperties();
$someService->newProperty = 5;
$someService->anotherNewProperty = 5;

?>
