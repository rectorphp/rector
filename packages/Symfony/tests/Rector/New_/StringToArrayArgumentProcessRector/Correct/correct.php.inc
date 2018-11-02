<?php

use Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\Source\Process;

$process = new Process(['ls', '-l']);

$process = new Process(['ls', '-l']);

$value = 123;
$process = new Process($value);
