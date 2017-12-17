<?php declare (strict_types=1);

namespace SomeNamespace;

use Symfony\Component\Process\ProcessBuilder;

$builder = new ProcessBuilder([]);
$process = $builder->getProcess();

$commandLine = (new ProcessBuilder('something'))->getProcess()->getCommandLine();

