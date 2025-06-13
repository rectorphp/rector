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
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\FuncCall\CurrentOnCollectionToArrayRector\CurrentOnCollectionToArrayRectorTest
 */
final class CurrentOnCollectionToArrayRector extends AbstractRector
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
        return new RuleDefinition('Change current() on Collection typed property to ->toArray() call, to always provide an array', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SimpleClass
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    public function merge()
    {
        return current($this->items);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SimpleClass
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    public function merge()
    {
        return current($this->items->toArray());
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
    public function refactor(Node $node) : ?FuncCall
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node->name, 'current')) {
            return null;
        }
        $secondArg = $node->getArgs()[0];
        if (!$this->collectionTypeDetector->isCollectionType($secondArg->value)) {
            return null;
        }
        $secondArg->value = new MethodCall($secondArg->value, 'toArray');
        return $node;
    }
}
