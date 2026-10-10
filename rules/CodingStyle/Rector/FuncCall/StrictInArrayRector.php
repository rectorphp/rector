<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\StrictInArrayRector\StrictInArrayRectorTest
 */
final class StrictInArrayRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TypeComparator $typeComparator;
    public function __construct(TypeComparator $typeComparator)
    {
        $this->typeComparator = $typeComparator;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Set in_array strict to true when defined on similar type', [new CodeSample(<<<'CODE_SAMPLE'
class BothStrings
{
    public function run(string $value)
    {
        return in_array($value, ['one', 'two', 'three']);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class BothStrings
{
    public function run(string $value)
    {
        return in_array($value, ['one', 'two', 'three'], true);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node, 'in_array')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $node->getArgs();
        if (count($args) !== 2) {
            return null;
        }
        $firstArgType = $this->nodeTypeResolver->getNativeType($args[0]->value);
        $secondArgType = $this->nodeTypeResolver->getNativeType($args[1]->value);
        if (!$secondArgType->isArray()->yes()) {
            return null;
        }
        if ($this->typeComparator->isSubtype($secondArgType->getIterableValueType(), $firstArgType)) {
            $node->args[] = new Arg($this->nodeFactory->createTrue());
            return $node;
        }
        return null;
    }
}
