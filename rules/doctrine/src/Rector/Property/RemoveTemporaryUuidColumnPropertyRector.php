<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\Doctrine\Tests\Rector\Property\RemoveTemporaryUuidColumnPropertyRector\RemoveTemporaryUuidColumnPropertyRectorTest
 */
final class RemoveTemporaryUuidColumnPropertyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove temporary $uuid property', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Column
{
    /**
     * @ORM\Column
     */
    public $id;

    /**
     * @ORM\Column
     */
    public $uuid;
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Column
{
    /**
     * @ORM\Column
     */
    public $id;
}
CODE_SAMPLE
            ),
        ]);
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

        if (! $this->hasPhpDocTagValueNode($node, ColumnTagValueNode::class)) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }
}
