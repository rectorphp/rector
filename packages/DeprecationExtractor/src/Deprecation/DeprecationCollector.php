<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Deprecation;

use PhpParser\Node;
use PhpParser\Node\Arg;

final class DeprecationCollector
{
    /**
     * @var string[]|Node[]
     */
    private $deprecationMessages = [];

    /**
     * @var Arg[]
     */
    private $deprecationArgNodes = [];

    public function addDeprecationMessage(string $deprecationMessage, Node $node): void
    {
        $this->deprecationMessages[] = [
            'message' => $deprecationMessage,
            'node' => $node,
        ];
    }

    public function addDeprecationArgNode(Arg $argNode): void
    {
        $this->deprecationArgNodes[] = $argNode;
    }

    /**
     * @return string[]|Node[]
     */
    public function getDeprecationMessages(): array
    {
        return $this->deprecationMessages;
    }

    /**
     * @return Arg[]
     */
    public function getDeprecationArgNodes(): array
    {
        return $this->deprecationArgNodes;
    }
}
