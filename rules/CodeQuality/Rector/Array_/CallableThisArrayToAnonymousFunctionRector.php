<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\Php\PhpMethodReflection;
use Rector\CodeQuality\NodeAnalyzer\CallableClassMethodMatcher;
use Rector\CodeQuality\NodeFactory\AnonymousFunctionFactory;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/language.types.callable.php#117260
 * @see https://3v4l.org/MsMbQ
 * @see https://3v4l.org/KM1Ji
 *
 * @see \Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\CallableThisArrayToAnonymousFunctionRectorTest
 */
final class CallableThisArrayToAnonymousFunctionRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var CallableClassMethodMatcher
     */
    private $callableClassMethodMatcher;
    /**
     * @var AnonymousFunctionFactory
     */
    private $anonymousFunctionFactory;
    public function __construct(\Rector\CodeQuality\NodeAnalyzer\CallableClassMethodMatcher $callableClassMethodMatcher, \Rector\CodeQuality\NodeFactory\AnonymousFunctionFactory $anonymousFunctionFactory)
    {
        $this->callableClassMethodMatcher = $callableClassMethodMatcher;
        $this->anonymousFunctionFactory = $anonymousFunctionFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert [$this, "method"] to proper anonymous function', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $values = [1, 5, 3];
        usort($values, [$this, 'compareSize']);

        return $values;
    }

    private function compareSize($first, $second)
    {
        return $first <=> $second;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $values = [1, 5, 3];
        usort($values, function ($first, $second) {
            return $this->compareSize($first, $second);
        });

        return $values;
    }

    private function compareSize($first, $second)
    {
        return $first <=> $second;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Array_::class];
    }
    /**
     * @param Array_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkipArray($node)) {
            return null;
        }
        $firstArrayItem = $node->items[0];
        if (!$firstArrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
            return null;
        }
        $objectVariable = $firstArrayItem->value;
        if (!$objectVariable instanceof \PhpParser\Node\Expr\Variable && !$objectVariable instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return null;
        }
        $secondArrayItem = $node->items[1];
        if (!$secondArrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
            return null;
        }
        $methodName = $secondArrayItem->value;
        if (!$methodName instanceof \PhpParser\Node\Scalar\String_) {
            return null;
        }
        $phpMethodReflection = $this->callableClassMethodMatcher->match($objectVariable, $methodName);
        if (!$phpMethodReflection instanceof \PHPStan\Reflection\Php\PhpMethodReflection) {
            return null;
        }
        return $this->anonymousFunctionFactory->create($phpMethodReflection, $objectVariable);
    }
    private function shouldSkipArray(\PhpParser\Node\Expr\Array_ $array) : bool
    {
        // callback is exactly "[$two, 'items']"
        if (\count($array->items) !== 2) {
            return \true;
        }
        // can be totally empty in case of "[, $value]"
        if ($array->items[0] === null) {
            return \true;
        }
        if ($array->items[1] === null) {
            return \true;
        }
        return $this->isCallbackAtFunctionNames($array, ['register_shutdown_function', 'forward_static_call']);
    }
    /**
     * @param string[] $functionNames
     */
    private function isCallbackAtFunctionNames(\PhpParser\Node\Expr\Array_ $array, array $functionNames) : bool
    {
        $parentNode = $array->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node\Arg) {
            return \false;
        }
        $parentParentNode = $parentNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentParentNode instanceof \PhpParser\Node\Expr\FuncCall) {
            return \false;
        }
        return $this->isNames($parentParentNode, $functionNames);
    }
}
