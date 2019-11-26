<?php

declare(strict_types=1);

namespace Rector\Doctrine\NodeFactory;

use PhpParser\Node\Stmt\Property;
use Rector\Doctrine\PhpDocParser\Ast\PhpDoc\PhpDocTagNodeFactory;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PhpParser\Node\NodeFactory;

final class EntityIdNodeFactory
{
    /**
     * @var PhpDocTagNodeFactory
     */
    private $phpDocTagNodeFactory;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(
        PhpDocTagNodeFactory $phpDocTagNodeFactory,
        DocBlockManipulator $docBlockManipulator,
        NodeFactory $nodeFactory
    ) {
        $this->phpDocTagNodeFactory = $phpDocTagNodeFactory;
        $this->docBlockManipulator = $docBlockManipulator;
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
        // add @var int
        $this->docBlockManipulator->addTag($property, $this->phpDocTagNodeFactory->createVarTagInt());

        // add @ORM\Id
        $this->docBlockManipulator->addTag($property, $this->phpDocTagNodeFactory->createIdTag());

        $this->docBlockManipulator->addTag($property, $this->phpDocTagNodeFactory->createIdColumnTag());

        $this->docBlockManipulator->addTag($property, $this->phpDocTagNodeFactory->createGeneratedValueTag('AUTO'));
    }
}
