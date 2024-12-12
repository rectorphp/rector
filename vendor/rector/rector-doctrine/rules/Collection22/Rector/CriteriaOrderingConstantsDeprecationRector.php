<?php

declare (strict_types=1);
namespace Rector\Doctrine\Collection22\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see
 */
final class CriteriaOrderingConstantsDeprecationRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ObjectType $criteriaObjectType;
    public function __construct()
    {
        $this->criteriaObjectType = new ObjectType('Doctrine\\Common\\Collections\\Criteria');
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace ASC/DESC with enum Ordering in Criteria::orderBy method call, and remove usage of Criteria::ASC and Criteria::DESC constants elsewhere', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Criteria;

$criteria = new Criteria();
$criteria->orderBy(['someProperty' => 'ASC', 'anotherProperty' => 'DESC']);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Criteria;

$criteria = new Criteria();
$criteria->orderBy(['someProperty' => \Doctrine\Common\Collections\Order::Ascending, 'anotherProperty' => \Doctrine\Common\Collections\Order::Descending]);
CODE_SAMPLE
), new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Criteria;

$query->addOrderBy('something', Criteria::ASC);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Criteria;

$query->addOrderBy('something', 'ASC');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, ClassConstFetch::class];
    }
    /**
     * @param MethodCall|ClassConstFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof ClassConstFetch) {
            return $this->refactorClassConstFetch($node);
        }
        return $this->refactorMethodCall($node);
    }
    private function refactorClassConstFetch(ClassConstFetch $classConstFetch) : ?Node
    {
        if (!$classConstFetch->name instanceof Identifier) {
            return null;
        }
        if (!\in_array($classConstFetch->name->name, ['ASC', 'DESC'])) {
            return null;
        }
        if (!$classConstFetch->class instanceof Name) {
            return null;
        }
        if (!$this->criteriaObjectType->isSuperTypeOf(new ObjectType($classConstFetch->class->toCodeString()))->yes()) {
            return null;
        }
        switch ($classConstFetch->name->name) {
            case 'ASC':
                return new String_('ASC');
            case 'DESC':
                return new String_('DESC');
        }
    }
    private function refactorMethodCall(MethodCall $methodCall) : ?Node
    {
        if (!$this->isName($methodCall->name, 'orderBy')) {
            return null;
        }
        if ($methodCall->isFirstClassCallable()) {
            return null;
        }
        if (!$this->criteriaObjectType->isSuperTypeOf($this->nodeTypeResolver->getType($methodCall->var))->yes()) {
            return null;
        }
        $args = $methodCall->getArgs();
        if (\count($args) < 1) {
            return null;
        }
        $arg = $args[0];
        if (!$arg instanceof Arg) {
            return null;
        }
        if (!$arg->value instanceof Array_) {
            return null;
        }
        $nodeHasChange = \false;
        $newItems = [];
        // we parse the array of the first argument
        foreach ($arg->value->items as $item) {
            if ($item === null) {
                $newItems[] = $item;
                continue;
            }
            if ($item->value instanceof String_ && \in_array($v = \strtoupper($item->value->value), ['ASC', 'DESC'], \true)) {
                $newItems[] = $this->buildArrayItem($v, $item->key);
                $nodeHasChange = \true;
            } elseif ($item->value instanceof ClassConstFetch && $item->value->class instanceof Name && $this->criteriaObjectType->isSuperTypeOf(new ObjectType($item->value->class->toString())) && $item->value->name instanceof Identifier && \in_array($v = \strtoupper((string) $item->value->name), ['ASC', 'DESC'], \true)) {
                $newItems[] = $this->buildArrayItem($v, $item->key);
                $nodeHasChange = \true;
            } else {
                $newItems[] = $item;
            }
        }
        if ($nodeHasChange) {
            return $this->nodeFactory->createMethodCall($methodCall->var, 'orderBy', $this->nodeFactory->createArgs([$this->nodeFactory->createArg(new Array_($newItems))]));
        }
        return null;
    }
    /**
     * @param 'ASC'|'DESC' $direction
     */
    private function buildArrayItem(string $direction, ?\PhpParser\Node\Expr $key) : ArrayItem
    {
        $classConstFetch = $this->nodeFactory->createClassConstFetch('Doctrine\\Common\\Collections\\Order', (function () use($direction) {
            switch ($direction) {
                case 'ASC':
                    return 'Ascending';
                case 'DESC':
                    return 'Descending';
            }
        })());
        return new ArrayItem($classConstFetch, $key);
    }
}
