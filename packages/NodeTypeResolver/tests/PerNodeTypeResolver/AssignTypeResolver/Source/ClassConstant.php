<?php declare(strict_types=1);

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AssignTypeResolver\Source\ClassWithParent;

$configurator = new ClassWithParent();
$container = $configurator->{__CLASS__}();
