<?php declare (strict_types=1);

namespace SomeNamespace;

use Rector\Symfony\Tests\Rector\Process\ProcessBuilderInstanceRector\Source\ProcessBuilder;

$process = ProcessBuilder::create('something')->getProcess();
