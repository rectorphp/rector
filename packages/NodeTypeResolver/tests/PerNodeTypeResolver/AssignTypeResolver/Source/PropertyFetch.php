<?php

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AssignTypeResolver\Source\ClassWithParent;

$someService = new ClassWithParent();
$variable = $someService->property;
