<?php

namespace Rector\Core\Tests\Rector\Property\RenamePropertyRector\Fixture;

use Rector\Core\Tests\Rector\Property\RenamePropertyRector\Source\ClassWithProperties;

$someService = new ClassWithProperties();
$someService->oldProperty = 5;
$someService->anotherOLDProperty = 5;

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Property\RenamePropertyRector\Fixture;

use Rector\Core\Tests\Rector\Property\RenamePropertyRector\Source\ClassWithProperties;

$someService = new ClassWithProperties();
$someService->newProperty = 5;
$someService->anotherNewProperty = 5;

?>
