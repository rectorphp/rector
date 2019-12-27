<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\Doctrine\Rector\Property\RemoveTemporaryUuidColumnPropertyRector
 */
final class RemoveTemporaryUuidColumnPropertyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove temporary $uuid property');
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'uuid')) {
            return null;
        }

        $phpDocInfo = $this->getPhpDocInfo($node);
        if ($phpDocInfo === null) {
            return null;
        }

        if (! $phpDocInfo->hasByType(ColumnTagValueNode::class)) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }
}
