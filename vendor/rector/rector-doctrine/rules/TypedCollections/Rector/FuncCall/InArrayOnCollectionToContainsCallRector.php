<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use Rector\Doctrine\TypedCollections\TypeAnalyzer\CollectionTypeDetector;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\FuncCall\InArrayOnCollectionToContainsCallRector\InArrayOnCollectionToContainsCallRectorTest
 */
final class InArrayOnCollectionToContainsCallRector extends AbstractRector
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
        return new RuleDefinition('Change in_array() on Collection typed property to ->contains() call', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class InArrayOnAssignedVariable
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    public function run()
    {
        return in_array('item', $this->items);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class InArrayOnAssignedVariable
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    public function run()
    {
        return $this->items->contains('item');
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?MethodCall
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node, 'in_array')) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        $secondArg = $node->getArgs()[1];
        if (!$this->collectionTypeDetector->isCollectionType($secondArg->value)) {
            return null;
        }
        return new MethodCall($secondArg->value, 'contains', [$firstArg]);
    }
}
