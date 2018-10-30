<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\CountOnNullRector\Wrong;

$result = [];
$nodes = [];
foreach ($nodes as $node) {
    $result[] = [
        'children' => $node->hasChildren(),
        'id' => $node['id'],
    ];
}
