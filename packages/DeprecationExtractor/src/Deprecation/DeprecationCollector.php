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
    private $deprecationArgNodes = [];

    public function addDeprecationMessage(string $deprecationMessage): void
    {
        $this->deprecationMessages[] = $deprecationMessage;
    }

    public function addDeprecationArgNode(Arg $argNode): void
    {
        $this->deprecationArgNodes[] = $argNode;
    }

    /**
     * @return string[]
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
