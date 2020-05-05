<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\Doctrine\Tests\Rector\Property\RemoveTemporaryUuidColumnPropertyRector\RemoveTemporaryUuidColumnPropertyRectorTest
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

        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
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
