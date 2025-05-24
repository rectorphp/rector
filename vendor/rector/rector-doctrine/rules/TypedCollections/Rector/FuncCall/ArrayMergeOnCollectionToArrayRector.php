<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\FuncCall\ArrayMergeOnCollectionToArrayRector\ArrayMergeOnCollectionToArrayRectorTest
 */
final class ArrayMergeOnCollectionToArrayRector extends AbstractRector
{
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change array_merge() on Collection to ->toArray()', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SetFirstParameterArray
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    public function merge()
    {
        return array_merge([], $this->items);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SetFirstParameterArray
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    public function merge()
    {
        return array_merge([], $this->items->toArray());
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?FuncCall
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node->name, 'array_merge')) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getArgs() as $arg) {
            $argType = $this->getType($arg->value);
            if (!$argType instanceof FullyQualifiedObjectType) {
                continue;
            }
            if ($argType->getClassName() !== DoctrineClass::COLLECTION) {
                continue;
            }
            $arg->value = new MethodCall($arg->value, 'toArray');
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
