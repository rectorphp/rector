<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitor;
use RectorPrefix202506\PHPUnit\Framework\Assert;
use Rector\Doctrine\TypedCollections\TypeAnalyzer\CollectionTypeDetector;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\Expression\RemoveAssertNotNullOnCollectionRector\RemoveAssertNotNullOnCollectionRectorTest
 */
final class RemoveAssertNotNullOnCollectionRector extends AbstractRector
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
        return new RuleDefinition('Remove ' . Assert::class . '::assertNotNull() on a Collection type', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

class SomeClass
{
    public function run(Collection $collection): void
    {
        Assert::assertNotNull($collection);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

class SomeClass
{
    public function run(Collection $collection): void
    {
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Expression::class];
    }
    /**
     * @param Expression $node
     */
    public function refactor(Node $node) : ?int
    {
        if (!$node->expr instanceof StaticCall) {
            return null;
        }
        $staticCall = $node->expr;
        if (!$this->isName($staticCall->name, 'assertNotNull')) {
            return null;
        }
        if (!$this->isName($staticCall->class, PHPUnitClassName::ASSERT)) {
            return null;
        }
        $firstArg = $staticCall->getArgs()[0];
        if (!$this->collectionTypeDetector->isCollectionType($firstArg->value)) {
            return null;
        }
        return NodeVisitor::REMOVE_NODE;
    }
}
