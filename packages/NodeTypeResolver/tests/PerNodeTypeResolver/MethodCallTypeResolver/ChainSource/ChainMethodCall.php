<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver\ChainSource;

$tableStyle = new ChainTableStyle();
$tableStyle->setCrossingChar(' ')
    ->setHorizontalBorderChar('<fg=magenta>-</>');
