<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Doctrine\TypedCollections\NodeAnalyzer\FreshArrayCollectionAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\ClassMethod\CollectionDocblockGenericTypeRector\CollectionDocblockGenericTypeRectorTest
 */
final class CollectionDocblockGenericTypeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private FreshArrayCollectionAnalyzer $freshArrayCollectionAnalyzer;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    public function __construct(BetterNodeFinder $betterNodeFinder, FreshArrayCollectionAnalyzer $freshArrayCollectionAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->freshArrayCollectionAnalyzer = $freshArrayCollectionAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add more precise generics type to method that returns Collection', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

final class SomeClass
{
    public function getItems(): Collection
    {
        $collection = new ArrayCollection();
        $collection->add(new SomeClass());

        return $collection;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

final class SomeClass
{
    /**
     * @return Collection<int, SomeClass>
     */
    public function getItems(): Collection
    {
        $collection = new ArrayCollection();
        $collection->add(new SomeClass());

        return $collection;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?ClassMethod
    {
        if ($node->stmts === null) {
            return null;
        }
        if ($node->returnType === null) {
            return null;
        }
        if (!$this->isName($node->returnType, DoctrineClass::COLLECTION)) {
            return null;
        }
        if (!$this->freshArrayCollectionAnalyzer->doesReturnNewArrayCollectionVariable($node)) {
            return null;
        }
        // we have a match here!
        // lets resolve docblock of this method
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        // is known and generic? lets skip it
        if ($returnTagValueNode instanceof ReturnTagValueNode && $returnTagValueNode->type instanceof GenericTypeNode) {
            return null;
        }
        // resolve type added to collection
        $methodCalls = $this->betterNodeFinder->findInstancesOfScoped([$node], MethodCall::class);
        $collectionAddMethodCalls = array_filter($methodCalls, function (MethodCall $methodCall): bool {
            if (!$this->isName($methodCall->name, 'add')) {
                return \false;
            }
            $callerType = $this->getType($methodCall->var);
            if (!$callerType instanceof ObjectType) {
                return \false;
            }
            return $callerType->isInstanceOf(DoctrineClass::ARRAY_COLLECTION)->yes();
        });
        if ($collectionAddMethodCalls === []) {
            return null;
        }
        $setTypeClasses = $this->resolveSetTypeClasses($collectionAddMethodCalls);
        if (count($setTypeClasses) !== 1) {
            return null;
        }
        $setTypeClass = $setTypeClasses[0];
        // add a new one with generic type
        // find return
        // find new ArrayCollection()
        // improve return type
        $genericTypeNode = new GenericTypeNode(new FullyQualifiedIdentifierTypeNode(DoctrineClass::COLLECTION), [new IdentifierTypeNode('int'), new FullyQualifiedIdentifierTypeNode($setTypeClass)]);
        $returnTagValueNode = new ReturnTagValueNode($genericTypeNode, '');
        $phpDocInfo->addTagValueNode($returnTagValueNode);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return null;
    }
    /**
     * @param MethodCall[] $collectionAddMethodCalls
     * @return string[]
     */
    private function resolveSetTypeClasses(array $collectionAddMethodCalls): array
    {
        $setTypeClasses = [];
        foreach ($collectionAddMethodCalls as $collectionAddMethodCall) {
            $setArg = $collectionAddMethodCall->getArgs()[0];
            $setType = $this->getType($setArg->value);
            if (!isset($setType->getObjectClassNames()[0])) {
                continue;
            }
            $setTypeClasses[] = $setType->getObjectClassNames()[0];
        }
        return array_unique($setTypeClasses);
    }
}
