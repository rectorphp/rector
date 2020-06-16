<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineRelationTagValueNodeInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\Doctrine\Tests\Rector\Property\RemoveTemporaryUuidRelationPropertyRector\RemoveTemporaryUuidRelationPropertyRectorTest
 */
final class RemoveTemporaryUuidRelationPropertyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove temporary *Uuid relation properties', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Column
{
    /**
     * @ORM\ManyToMany(targetEntity="Phonenumber")
     */
    private $apple;

    /**
     * @ORM\ManyToMany(targetEntity="Phonenumber")
     */
    private $appleUuid;
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
     * @ORM\ManyToMany(targetEntity="Phonenumber")
     */
    private $apple;
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
        if (! $this->isName($node, '*Uuid')) {
            return null;
        }

        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        if (! $phpDocInfo->hasByType(DoctrineRelationTagValueNodeInterface::class)) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }
}
