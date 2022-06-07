<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeFactory;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\Node\NodeFactory;
final class EntityIdNodeFactory
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(NodeFactory $nodeFactory, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->nodeFactory = $nodeFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function createIdProperty() : Property
    {
        $idProperty = $this->nodeFactory->createPrivateProperty('id');
        $this->decoratePropertyWithIdAnnotations($idProperty);
        return $idProperty;
    }
    private function decoratePropertyWithIdAnnotations(Property $property) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        // add @var int
        $identifierTypeNode = new IdentifierTypeNode('int');
        $varTagValueNode = new VarTagValueNode($identifierTypeNode, '', '');
        $phpDocInfo->addTagValueNode($varTagValueNode);
        // add @ORM\Id
        $phpDocTagNodes = [];
        $phpDocTagNodes[] = new SpacelessPhpDocTagNode('RectorPrefix20220607\\@ORM\\Id', new DoctrineAnnotationTagValueNode(new IdentifierTypeNode('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\Id'), null, []));
        $phpDocTagNodes[] = new SpacelessPhpDocTagNode('RectorPrefix20220607\\@ORM\\Column', new DoctrineAnnotationTagValueNode(new IdentifierTypeNode('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\Column'), null, ['type' => '"integer"']));
        $phpDocTagNodes[] = new SpacelessPhpDocTagNode('RectorPrefix20220607\\@ORM\\GeneratedValue', new DoctrineAnnotationTagValueNode(new IdentifierTypeNode('RectorPrefix20220607\\Doctrine\\ORM\\Mapping\\GeneratedValue'), null, ['strategy' => '"AUTO"']));
        foreach ($phpDocTagNodes as $phpDocTagNode) {
            $phpDocInfo->addPhpDocTagNode($phpDocTagNode);
        }
        $phpDocInfo->markAsChanged();
    }
}
