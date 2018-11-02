<?php

namespace Rector\Symfony\Tests\Rector\New_\RootNodeTreeBuilderRector\Wrong;

use Rector\Symfony\Tests\Rector\New_\RootNodeTreeBuilderRector\Source\TreeBuilder;

$treeBuilder = new TreeBuilder();
$treeBuilder->root('override', 'array', new \stdClass());
