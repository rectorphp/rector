<?php

$someService = new SomeClass;
$someService->oldProperty = 5;
$someService->anotherOLDProperty = 5;

?>
-----
<?php

$someService = new SomeClass;
$someService->newProperty = 5;
$someService->anotherNewProperty = 5;

?>
