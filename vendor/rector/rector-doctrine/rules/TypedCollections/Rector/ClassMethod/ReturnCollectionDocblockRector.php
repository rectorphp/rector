<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Generic\GenericObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\ClassMethod\ReturnCollectionDocblockRector\ReturnCollectionDocblockRectorTest
 */
final class ReturnCollectionDocblockRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, StaticTypeMapper $staticTypeMapper, DocBlockUpdater $docBlockUpdater)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add return Collection docblock to method that returns a collection property', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class OverrideMix
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    /**
     * @return Collection|string[]
     */
    public function getItems()
    {
        return $this->items;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class OverrideMix
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    /**
     * @return Collection<int, string>
     */
    public function getItems()
    {
        return $this->items;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?ClassMethod
    {
        if (!$node->isPublic() || $node->isAbstract()) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if ($classReflection->isInterface()) {
            return null;
        }
        if ($node->stmts === null || \count($node->stmts) !== 1) {
            return null;
        }
        $soleStmt = $node->stmts[0] ?? null;
        // must be return property
        if (!$soleStmt instanceof Return_) {
            return null;
        }
        if (!$soleStmt->expr instanceof PropertyFetch) {
            return null;
        }
        $propertyFetch = $soleStmt->expr;
        $scope = ScopeFetcher::fetch($propertyFetch);
        $propertyFetchType = $scope->getType($propertyFetch);
        if (!$propertyFetchType instanceof GenericObjectType) {
            return null;
        }
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $returnTagValueNode = $classMethodPhpDocInfo->getReturnTagValue();
        if ($returnTagValueNode instanceof ReturnTagValueNode) {
            // already set the correct type
            if ($returnTagValueNode->type instanceof GenericTypeNode) {
                return null;
            }
            $genericTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($propertyFetchType);
            $returnTagValueNode->type = $genericTypeNode;
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        } else {
            $genericTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($propertyFetchType);
            $returnTagValueNode = new ReturnTagValueNode($genericTypeNode, '');
            $classMethodPhpDocInfo->addTagValueNode($returnTagValueNode);
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        }
        return $node;
    }
}
