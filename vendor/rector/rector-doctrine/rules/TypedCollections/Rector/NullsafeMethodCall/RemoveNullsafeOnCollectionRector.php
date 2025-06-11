<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\NullsafeMethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use Rector\Doctrine\TypedCollections\TypeAnalyzer\CollectionTypeDetector;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\NullsafeMethodCall\RemoveNullsafeOnCollectionRector\RemoveNullsafeOnCollectionRectorTest
 */
final class RemoveNullsafeOnCollectionRector extends AbstractRector
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
        return new RuleDefinition('Remove nullsafe check on method call on a Collection type', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

class SomeClass
{
    private Collection $collection;

    public function run()
    {
        return $this->collection?->empty();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

class SomeClass
{
    private Collection $collection;

    public function run()
    {
        return $this->collection->empty();
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [NullsafeMethodCall::class];
    }
    /**
     * @param NullsafeMethodCall $node
     */
    public function refactor(Node $node) : ?MethodCall
    {
        if (!$this->collectionTypeDetector->isCollectionNonNullableType($node->var)) {
            return null;
        }
        return new MethodCall($node->var, $node->name, $node->args);
    }
}
