<?php declare (strict_types=1);

$compiler = new Nette\DI\Compiler;
$compiler->compile();
$compiler->addConfig($firstArg);
$compiler->setClassName($secondArg);

$someOtherValue = 5;
