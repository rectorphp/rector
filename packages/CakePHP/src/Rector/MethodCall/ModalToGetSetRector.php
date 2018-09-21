<?php declare(strict_types=1);

namespace Rector\CakePHP\Rector\MethodCall;

use PhpParser\Node;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\RectorDefinition;

final class ModalToGetSetRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {

    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
    }

    public function refactor(Node $node): ?Node
    {
    }
}