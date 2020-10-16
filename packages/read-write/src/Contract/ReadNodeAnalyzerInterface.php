<?php

declare(strict_types=1);

namespace Rector\ReadWrite\Contract;

use PhpParser\Node;

interface ReadNodeAnalyzerInterface
{
    public function supports(Node $node): bool;

    public function isRead(Node $node): bool;
}
