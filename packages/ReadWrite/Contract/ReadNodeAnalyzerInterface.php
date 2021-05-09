<?php

declare (strict_types=1);
namespace Rector\ReadWrite\Contract;

use PhpParser\Node;
interface ReadNodeAnalyzerInterface
{
    public function supports(\PhpParser\Node $node) : bool;
    public function isRead(\PhpParser\Node $node) : bool;
}
