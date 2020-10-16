<?php

declare(strict_types=1);

namespace Rector\ReadWrite\Contract;

use Rector\ReadWrite\NodeAnalyzer\ReadExprAnalyzer;

interface ReadExprAnalyzerAwareInterface
{
    public function setReadExprAnalyzer(ReadExprAnalyzer $readExprAnalyzer): void;
}
