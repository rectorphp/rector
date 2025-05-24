<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Doctrine\TypedCollections\TypeAnalyzer\CollectionTypeDetector;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\ClassMethod\CollectionGetterNativeTypeRector\CollectionGetterNativeTypeRectorTest
 */
final class CollectionGetterNativeTypeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private CollectionTypeDetector $collectionTypeDetector;
    public function __construct(CollectionTypeDetector $collectionTypeDetector)
    {
        $this->collectionTypeDetector = $collectionTypeDetector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add native return type to a Collection getter', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class ReturnPropertyCollectionWithArrayTypeDeclaration
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    public function getItems(): array
    {
        return $this->items;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class ReturnPropertyCollectionWithArrayTypeDeclaration
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    public function getItems(): Collection
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
        if ($node->stmts === null) {
            return null;
        }
        if ($node->returnType !== null && $this->isName($node->returnType, DoctrineClass::COLLECTION)) {
            return null;
        }
        if (\count($node->stmts) !== 1) {
            return null;
        }
        $soleStmt = $node->stmts[0];
        if (!$soleStmt instanceof Return_) {
            return null;
        }
        if (!$soleStmt->expr instanceof PropertyFetch) {
            return null;
        }
        if (!$this->collectionTypeDetector->isCollectionType($soleStmt->expr)) {
            return null;
        }
        $node->returnType = new FullyQualified(DoctrineClass::COLLECTION);
        return $node;
    }
}
