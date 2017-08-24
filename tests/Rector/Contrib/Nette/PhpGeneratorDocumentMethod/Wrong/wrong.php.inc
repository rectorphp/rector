<?php declare (strict_types=1);

$classType = new Nette\PhpGenerator\ClassType;
$classType->addDocument('...');


$method = new Nette\PhpGenerator\Method('someMethod');
$method->setDocuments('...');

$phpFile = new Nette\PhpGenerator\PhpFile;
$phpFile->getDocuments('...');

$property = new Nette\PhpGenerator\Property('someProperty');
$property->addDocument('...');
