<?php

namespace Rector\Generic\Tests\Rector\Property\RenamePropertyRector\Fixture;

use Rector\Generic\Tests\Rector\Property\RenamePropertyRector\Source\ClassWithProperties;

$someService = new ClassWithProperties();
$someService->oldProperty = 5;
$someService->anotherOLDProperty = 5;

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Property\RenamePropertyRector\Fixture;

use Rector\Generic\Tests\Rector\Property\RenamePropertyRector\Source\ClassWithProperties;

$someService = new ClassWithProperties();
$someService->newProperty = 5;
$someService->anotherNewProperty = 5;

?>
