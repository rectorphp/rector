<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeFactory;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
final class TranslationClassNodeFactory
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ClassInsertManipulator $classInsertManipulator)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->classInsertManipulator = $classInsertManipulator;
    }
    public function create(string $classShortName) : Class_
    {
        $class = new Class_($classShortName);
        $class->implements[] = new FullyQualified('Knp\\DoctrineBehaviors\\Contract\\Entity\\TranslationInterface');
        $this->classInsertManipulator->addAsFirstTrait($class, 'Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslationTrait');
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
        $spacelessPhpDocTagNode = new SpacelessPhpDocTagNode('@ORM\\Entity', new DoctrineAnnotationTagValueNode(new IdentifierTypeNode('Doctrine\\ORM\\Mapping\\Entity'), null, []));
        $phpDocInfo->addPhpDocTagNode($spacelessPhpDocTagNode);
        // traverse with node name resolver, to to comply with PHPStan default parser
        $nameResolver = new NameResolver(null, ['replaceNodes' => \false, 'preserveOriginalNames' => \true]);
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($nameResolver);
        $nodeTraverser->traverse([$class]);
        return $class;
    }
}
