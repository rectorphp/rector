<?php

namespace Rector\Renaming\Tests\Rector\PropertyFetch\RenamePropertyRector\Fixture;

use Rector\Renaming\Tests\Rector\PropertyFetch\RenamePropertyRector\Source\ClassWithProperties;

$someService = new ClassWithProperties();
$someService->oldProperty = 5;
$someService->anotherOLDProperty = 5;

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\PropertyFetch\RenamePropertyRector\Fixture;

use Rector\Renaming\Tests\Rector\PropertyFetch\RenamePropertyRector\Source\ClassWithProperties;

$someService = new ClassWithProperties();
$someService->newProperty = 5;
$someService->anotherNewProperty = 5;

?>
