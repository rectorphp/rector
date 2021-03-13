<?php

declare(strict_types=1);

namespace Rector\Doctrine\NodeFactory;

use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;
use Rector\BetterPhpDocParser\Printer\TagValueNodePrinter;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\GeneratedValueTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\IdTagValueNode;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Doctrine\PhpDocParser\Ast\PhpDoc\PhpDocTagNodeFactory;

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

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var ArrayPartPhpDocTagPrinter
     */
    private $arrayPartPhpDocTagPrinter;

    /**
     * @var TagValueNodePrinter
     */
    private $tagValueNodePrinter;

    public function __construct(
        NodeFactory $nodeFactory,
        PhpDocTagNodeFactory $phpDocTagNodeFactory,
        PhpDocInfoFactory $phpDocInfoFactory,
        ArrayPartPhpDocTagPrinter $arrayPartPhpDocTagPrinter,
        TagValueNodePrinter $tagValueNodePrinter
    ) {
        $this->phpDocTagNodeFactory = $phpDocTagNodeFactory;
        $this->nodeFactory = $nodeFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->arrayPartPhpDocTagPrinter = $arrayPartPhpDocTagPrinter;
        $this->tagValueNodePrinter = $tagValueNodePrinter;
    }

    public function createIdProperty(): Property
    {
        $uuidProperty = $this->nodeFactory->createPrivateProperty('id');

        $this->decoratePropertyWithIdAnnotations($uuidProperty);

        return $uuidProperty;
    }

    private function decoratePropertyWithIdAnnotations(Property $property): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        // add @var int
        $attributeAwareVarTagValueNode = $this->phpDocTagNodeFactory->createVarTagIntValueNode();
        $phpDocInfo->addTagValueNode($attributeAwareVarTagValueNode);

        // add @ORM\Id
        $idTagValueNode = new IdTagValueNode($this->arrayPartPhpDocTagPrinter, $this->tagValueNodePrinter);
        $phpDocInfo->addTagValueNodeWithShortName($idTagValueNode);

        $idColumnTagValueNode = $this->phpDocTagNodeFactory->createIdColumnTagValueNode();
        $phpDocInfo->addTagValueNodeWithShortName($idColumnTagValueNode);

        $generatedValueTagValueNode = new GeneratedValueTagValueNode(
            $this->arrayPartPhpDocTagPrinter,
            $this->tagValueNodePrinter,
            [
                'strategy' => 'AUTO',
            ]);
        $phpDocInfo->addTagValueNodeWithShortName($generatedValueTagValueNode);
    }
}
