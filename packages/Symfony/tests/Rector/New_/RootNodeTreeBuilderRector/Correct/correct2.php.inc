<?php

namespace Rector\Symfony\Tests\Rector\New_\RootNodeTreeBuilderRector\Wrong;

use Rector\Symfony\Tests\Rector\New_\RootNodeTreeBuilderRector\Source\TreeBuilder;

$treeBuilder = new TreeBuilder('override', 'array', new \stdClass());
$treeBuilder->getRootNode();
