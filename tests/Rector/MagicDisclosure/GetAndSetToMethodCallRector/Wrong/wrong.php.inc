<?php declare(strict_types=1);

use Rector\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Source\SomeContainer;

$container = new SomeContainer();
$someService = $container->someService;

$parameters = $container->parameters;
