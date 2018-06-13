<?php

use Symfony\Component\Console\Helper\TableStyle;

$tableStyle = new TableStyle();
$tableStyle->oldMethod(' ')
    ->anotherOldMethod('<fg=magenta>-</>');
