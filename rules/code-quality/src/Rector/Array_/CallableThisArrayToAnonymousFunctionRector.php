<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\CodeQuality\NodeAnalyzer\CallableClassMethodMatcher;
use Rector\CodeQuality\NodeFactory\AnonymousFunctionFactory;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://www.php.net/manual/en/language.types.callable.php#117260
 * @see https://3v4l.org/MsMbQ
 * @see https://3v4l.org/KM1Ji
 *
 * @see \Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\CallableThisArrayToAnonymousFunctionRectorTest
 */
final class CallableThisArrayToAnonymousFunctionRector extends AbstractRector
{
    /**
     * @var CallableClassMethodMatcher
     */
    private $callableClassMethodMatcher;

    /**
     * @var AnonymousFunctionFactory
     */
    private $anonymousFunctionFactory;

    public function __construct(
        CallableClassMethodMatcher $callableClassMethodMatcher,
        AnonymousFunctionFactory $anonymousFunctionFactory
    ) {
        $this->callableClassMethodMatcher = $callableClassMethodMatcher;
        $this->anonymousFunctionFactory = $anonymousFunctionFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Convert [$this, "method"] to proper anonymous function',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
                    ,
                    <<<'CODE_SAMPLE'
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
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Array_::class];
    }

    /**
     * @param Array_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipArray($node)) {
            return null;
        }

        $firstArrayItem = $node->items[0];
        if (! $firstArrayItem instanceof ArrayItem) {
            return null;
        }

        $objectVariable = $firstArrayItem->value;
        if (! $objectVariable instanceof Variable && ! $objectVariable instanceof PropertyFetch) {
            return null;
        }

        $secondArrayItem = $node->items[1];
        if (! $secondArrayItem instanceof ArrayItem) {
            return null;
        }

        $methodName = $secondArrayItem->value;
        if (! $methodName instanceof String_) {
            return null;
        }

        $classMethod = $this->callableClassMethodMatcher->match($objectVariable, $methodName);
        if (! $classMethod instanceof ClassMethod) {
            return null;
        }

        return $this->anonymousFunctionFactory->create($classMethod, $objectVariable);
    }

    private function shouldSkipArray(Array_ $array): bool
    {
        // callback is exactly "[$two, 'items']"
        if (count($array->items) !== 2) {
            return true;
        }

        // can be totally empty in case of "[, $value]"
        if ($array->items[0] === null) {
            return true;
        }

        if ($array->items[1] === null) {
            return true;
        }

        return $this->isCallbackAtFunctionName($array, 'register_shutdown_function');
    }

    private function isCallbackAtFunctionName(Array_ $array, string $functionName): bool
    {
        $parentNode = $array->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Arg) {
            return false;
        }

        $parentParentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentParentNode instanceof FuncCall) {
            return false;
        }

        return $this->isName($parentParentNode, $functionName);
    }
}
