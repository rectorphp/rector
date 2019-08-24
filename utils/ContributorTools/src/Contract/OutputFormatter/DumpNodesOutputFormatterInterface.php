<?php declare(strict_types=1);

namespace Rector\Utils\ContributorTools\Contract\OutputFormatter;

use Rector\Utils\ContributorTools\Node\NodeInfoResult;

interface DumpNodesOutputFormatterInterface
{
    public function getName(): string;

    public function format(NodeInfoResult $nodeInfoResult): void;
}
