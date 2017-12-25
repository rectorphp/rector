<?php declare(strict_types=1);

$container = new Nette\DI\Container;
$container->hasService('someService');
$container->removeService('someService');
