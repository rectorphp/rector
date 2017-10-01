<?php declare (strict_types=1);

$classType = new Nette\PhpGenerator\ClassType;
$classType->addComment('...');


$method = new Nette\PhpGenerator\Method('someMethod');
$method->setComment('...');

$phpFile = new Nette\PhpGenerator\PhpFile;
$phpFile->getComment('...');

$property = new Nette\PhpGenerator\Property('someProperty');
$property->addComment('...');
