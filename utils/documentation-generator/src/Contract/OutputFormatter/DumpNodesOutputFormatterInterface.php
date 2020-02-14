<?php

declare(strict_types=1);

namespace Rector\Utils\DocumentationGenerator\Contract\OutputFormatter;

use Rector\Utils\DocumentationGenerator\Node\NodeInfoResult;

interface DumpNodesOutputFormatterInterface
{
    public function getName(): string;

    public function format(NodeInfoResult $nodeInfoResult): void;
}
