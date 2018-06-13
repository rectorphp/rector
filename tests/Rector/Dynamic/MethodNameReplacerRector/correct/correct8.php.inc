<?php

use Symfony\Component\Console\Helper\TableStyle;

$tableStyle = new TableStyle();
$tableStyle->newMethod(' ')
    ->anotherNewMethod('<fg=magenta>-</>');
