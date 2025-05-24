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
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\FuncCall\ArrayMapOnCollectionToArrayRector\ArrayMapOnCollectionToArrayRectorTest
 */
final class ArrayMapOnCollectionToArrayRector extends AbstractRector
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
        return new RuleDefinition('Change array_map on Collection typed property to ->toArray() call, to always provide an array', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class ArrayMapOnAssignedVariable
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    public function merge()
    {
        $items = $this->items;

        return array_map(fn ($item) => $item, $items);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class ArrayMapOnAssignedVariable
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    public function merge()
    {
        $items = $this->items;

        return array_map(fn ($item) => $item, $items->toArray());
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
        if (!$this->isName($node->name, 'array_map')) {
            return null;
        }
        $secondArg = $node->getArgs()[1];
        if (!$this->collectionTypeDetector->isCollectionType($secondArg->value)) {
            return null;
        }
        $secondArg->value = new MethodCall($secondArg->value, 'toArray');
        return $node;
    }
}
