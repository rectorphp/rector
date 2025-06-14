<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\ClassMethod\NarrowArrayCollectionToCollectionRector\NarrowArrayCollectionToCollectionRectorTest
 */
final class NarrowArrayCollectionToCollectionRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Narrow ArrayCollection to Collection in class method and property', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\ArrayCollection;

final class ArrayCollectionGeneric
{
    /**
     * @return ArrayCollection<int, string>
     */
    public function someMethod()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\ArrayCollection;

final class ArrayCollectionGeneric
{
    /**
     * @return \Doctrine\Common\Collections\Collection<int, string>
     */
    public function someMethod()
    {
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Property::class];
    }
    /**
     * @param ClassMethod|Property $node
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property|null
     */
    public function refactor(Node $node)
    {
        if ($node instanceof ClassMethod) {
            return $this->refactorClassMethod($node);
        }
        return $this->refactorProperty($node);
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode $tagValueNode
     */
    private function processTagValueNode($tagValueNode) : bool
    {
        // 1. generic type
        if ($tagValueNode->type instanceof GenericTypeNode) {
            $genericTypeNode = $tagValueNode->type;
            if ($genericTypeNode->type->name === 'ArrayCollection') {
                $genericTypeNode->type = new IdentifierTypeNode('\\' . DoctrineClass::COLLECTION);
                return \true;
            }
        }
        // 2. union type
        if ($tagValueNode->type instanceof UnionTypeNode) {
            $unionTypeNode = $tagValueNode->type;
            foreach ($unionTypeNode->types as $key => $unionedType) {
                if ($unionedType instanceof IdentifierTypeNode) {
                    if (!\in_array($unionedType->name, ['ArrayCollection', DoctrineClass::ARRAY_COLLECTION], \true)) {
                        continue;
                    }
                    $unionTypeNode->types[$key] = new IdentifierTypeNode('\\' . DoctrineClass::COLLECTION);
                    return \true;
                }
                if ($unionedType instanceof GenericTypeNode && $unionedType->type->name === 'ArrayCollection') {
                    $unionedType->type = new IdentifierTypeNode('\\' . DoctrineClass::COLLECTION);
                    return \true;
                }
            }
        }
        // 3. handle single type
        if (!$tagValueNode->type instanceof IdentifierTypeNode) {
            return \false;
        }
        if ($tagValueNode->type->name !== 'ArrayCollection') {
            return \false;
        }
        $tagValueNode->type = new IdentifierTypeNode('\\' . DoctrineClass::COLLECTION);
        return \true;
    }
    private function refactorClassMethodNativeTypes(ClassMethod $classMethod) : bool
    {
        $hasChanged = \false;
        foreach ($classMethod->params as $param) {
            if ($this->processNativeType($param)) {
                $hasChanged = \true;
            }
        }
        if (!$classMethod->returnType instanceof Node) {
            return $hasChanged;
        }
        $hasReturnCollectionType = $this->hasCollectionName($classMethod);
        $this->traverseNodesWithCallable($classMethod->returnType, function (Node $node) use($hasReturnCollectionType, &$hasChanged) {
            if ($node instanceof Identifier && $this->isName($node, 'array')) {
                $hasChanged = \true;
                if ($hasReturnCollectionType) {
                    return NodeVisitorAbstract::REMOVE_NODE;
                }
            }
            if ($node instanceof Name && $this->isName($node, DoctrineClass::ARRAY_COLLECTION)) {
                $hasChanged = \true;
                if ($hasReturnCollectionType) {
                    // we already have Collection, and can remove it
                    return NodeVisitorAbstract::REMOVE_NODE;
                }
                return new FullyQualified(DoctrineClass::COLLECTION);
            }
            return null;
        });
        if ($this->isName($classMethod->returnType, DoctrineClass::ARRAY_COLLECTION)) {
            $classMethod->returnType = new FullyQualified(DoctrineClass::COLLECTION);
            $hasChanged = \true;
        }
        return $hasChanged;
    }
    private function refactorClassMethod(ClassMethod $classMethod) : ?ClassMethod
    {
        $hasChanged = $this->refactorClassMethodNativeTypes($classMethod);
        // docblocks
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if ($classMethodPhpDocInfo instanceof PhpDocInfo && $this->refactorClassMethodDocblock($classMethodPhpDocInfo)) {
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
        return $classMethod;
    }
    /**
     * @param \PhpParser\Node\Param|\PhpParser\Node\Stmt\Property $paramOrProperty
     */
    private function processNativeType($paramOrProperty) : bool
    {
        if (!$paramOrProperty->type instanceof Node) {
            return \false;
        }
        $hasCollectionName = $this->hasCollectionName($paramOrProperty);
        $hasChanged = \false;
        if ($this->isName($paramOrProperty->type, DoctrineClass::ARRAY_COLLECTION)) {
            $paramOrProperty->type = new FullyQualified(DoctrineClass::COLLECTION);
            return \true;
        }
        $this->traverseNodesWithCallable($paramOrProperty->type, function (Node $node) use($hasCollectionName, &$hasChanged) {
            if (!$node instanceof Name) {
                return null;
            }
            if (!$this->isName($node, DoctrineClass::ARRAY_COLLECTION)) {
                return null;
            }
            $hasChanged = \true;
            if ($hasCollectionName) {
                // we already have Collection, and can remove it
                return NodeVisitorAbstract::REMOVE_NODE;
            }
            return new FullyQualified(DoctrineClass::COLLECTION);
        });
        return $hasChanged;
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param|\PhpParser\Node\Stmt\ClassMethod $stmts
     */
    private function hasCollectionName($stmts) : bool
    {
        $typeNode = $stmts instanceof ClassMethod ? $stmts->returnType : $stmts->type;
        if (!$typeNode instanceof Node) {
            return \false;
        }
        $nodeFinder = new NodeFinder();
        $collectionName = $nodeFinder->findFirst($typeNode, function (Node $node) : bool {
            if (!$node instanceof Name) {
                return \false;
            }
            return $node->toString() === DoctrineClass::COLLECTION;
        });
        return $collectionName instanceof Name;
    }
    private function refactorClassMethodDocblock(PhpDocInfo $classMethodPhpDocInfo) : bool
    {
        $hasChanged = \false;
        // return tag
        $returnTagValueNode = $classMethodPhpDocInfo->getReturnTagValue();
        if ($returnTagValueNode instanceof ReturnTagValueNode && $this->processTagValueNode($returnTagValueNode)) {
            $hasChanged = \true;
        }
        // param tags
        foreach ($classMethodPhpDocInfo->getParamTagValueNodes() as $paramTagValueNode) {
            if ($this->processTagValueNode($paramTagValueNode)) {
                $hasChanged = \true;
            }
        }
        return $hasChanged;
    }
    private function refactorProperty(Property $property) : ?\PhpParser\Node\Stmt\Property
    {
        $hasChanged = \false;
        if ($this->processNativeType($property)) {
            $hasChanged = \true;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if ($varTagValueNode instanceof VarTagValueNode && $this->processTagValueNode($varTagValueNode)) {
            $hasChanged = \true;
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($property);
        }
        if ($hasChanged) {
            return $property;
        }
        return null;
    }
}
