<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeFactory;

use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
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
        $phpDocTagNodes[] = new SpacelessPhpDocTagNode('@ORM\\Id', new DoctrineAnnotationTagValueNode(new IdentifierTypeNode('Doctrine\\ORM\\Mapping\\Id'), null, []));
        $phpDocTagNodes[] = new SpacelessPhpDocTagNode('@ORM\\Column', new DoctrineAnnotationTagValueNode(new IdentifierTypeNode('Doctrine\\ORM\\Mapping\\Column'), null, [new ArrayItemNode('integer', 'type', String_::KIND_DOUBLE_QUOTED)]));
        $phpDocTagNodes[] = new SpacelessPhpDocTagNode('@ORM\\GeneratedValue', new DoctrineAnnotationTagValueNode(new IdentifierTypeNode('Doctrine\\ORM\\Mapping\\GeneratedValue'), null, [new ArrayItemNode('AUTO', 'strategy', String_::KIND_DOUBLE_QUOTED)]));
        foreach ($phpDocTagNodes as $phpDocTagNode) {
            $phpDocInfo->addPhpDocTagNode($phpDocTagNode);
        }
        $phpDocInfo->markAsChanged();
    }
}
