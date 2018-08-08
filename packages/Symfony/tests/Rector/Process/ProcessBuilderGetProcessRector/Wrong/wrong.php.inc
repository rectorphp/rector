<?php declare (strict_types=1);

namespace SomeNamespace;

use Rector\Symfony\Tests\Rector\Process\ProcessBuilderGetProcessRector\Source\ProcessBuilder;

$builder = new ProcessBuilder([]);
$process = $builder->getProcess();

$commandLine = $builder->getProcess()->getCommandLine();
