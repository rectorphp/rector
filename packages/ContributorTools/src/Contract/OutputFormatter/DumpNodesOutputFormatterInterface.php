<?php declare(strict_types=1);

namespace Rector\ContributorTools\Contract\OutputFormatter;

use Rector\ContributorTools\Node\NodeInfoResult;

interface DumpNodesOutputFormatterInterface
{
    public function getName(): string;

    public function format(NodeInfoResult $nodeInfoResult): void;
}
