<?php declare(strict_types=1);

$compiler = new \Symfony\Component\DependencyInjection\Compiler\Compiler();
$compiler->addPass($somePass, 5, 0);
