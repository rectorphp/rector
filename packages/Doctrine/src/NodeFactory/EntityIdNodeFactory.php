<?php

declare(strict_types=1);

namespace Rector\Doctrine\NodeFactory;

use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\GeneratedValueTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\IdTagValueNode;
use Rector\Doctrine\PhpDocParser\Ast\PhpDoc\PhpDocTagNodeFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\NodeFactory;

final class EntityIdNodeFactory
{
    /**
     * @var PhpDocTagNodeFactory
     */
    private $phpDocTagNodeFactory;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(PhpDocTagNodeFactory $phpDocTagNodeFactory, NodeFactory $nodeFactory)
    {
        $this->phpDocTagNodeFactory = $phpDocTagNodeFactory;
        $this->nodeFactory = $nodeFactory;
    }

    public function createIdProperty(): Property
    {
        $uuidProperty = $this->nodeFactory->createPrivateProperty('id');

        $this->decoratePropertyWithIdAnnotations($uuidProperty);

        return $uuidProperty;
    }

    public function decoratePropertyWithIdAnnotations(Property $property): void
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);

        // add @var int
        $varTagValueNode = $this->phpDocTagNodeFactory->createVarTagIntValueNode();
        $phpDocInfo->addTagValueNode($varTagValueNode);

        // add @ORM\Id
        $phpDocInfo->addTagValueNodeWithShortName(new IdTagValueNode());

        $idColumnTagValueNode = $this->phpDocTagNodeFactory->createIdColumnTagValueNode();
        $phpDocInfo->addTagValueNodeWithShortName($idColumnTagValueNode);

        $generatedValueTagValueNode = new GeneratedValueTagValueNode('AUTO');
        $phpDocInfo->addTagValueNodeWithShortName($generatedValueTagValueNode);
    }
}
