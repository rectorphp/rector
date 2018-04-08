<?php

use Rector\NodeTypeResolver\Tests\Source\ClassWithFluentNonSelfReturn;

$classWithFluentNonSelfReturn = new ClassWithFluentNonSelfReturn();
$classWithFluentNonSelfReturn->createAnotherClass()
    ->callAndReturnSelf()
    ->getParameters();
