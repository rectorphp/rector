<?php declare (strict_types=1);

$compiler = new Nette\DI\Compiler;
$compiler->setClassName($firstArg);
$compiler->generateCode();

$someOtherValue = 5;
