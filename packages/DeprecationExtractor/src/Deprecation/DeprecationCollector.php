<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Deprecation;

use PhpParser\Node\Arg;

final class DeprecationCollector
{
    /**
     * @var string[]
     */
    private $deprecationMessages = [];

    /**
     * @var Arg[]
     */
    private $deprecationNodes = [];

    public function addDeprecationMessage(string $deprecationMessage): void
    {
        $this->deprecationMessages[] = $deprecationMessage;
    }

    public function addDeprecationNode(Arg $argNode): void
    {
        $this->deprecationNodes[] = $argNode;
    }
}
