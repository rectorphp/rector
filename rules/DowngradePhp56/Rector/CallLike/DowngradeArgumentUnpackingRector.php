<?php

declare (strict_types=1);
namespace Rector\DowngradePhp56\Rector\CallLike;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp56\NodeManipulator\ArgManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220501\Webmozart\Assert\Assert;
/**
 * @changelog https://wiki.php.net/rfc/argument_unpacking
 *
 * @see \Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\DowngradeArgumentUnpackingRectorTest
 */
final class DowngradeArgumentUnpackingRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DowngradePhp56\NodeManipulator\ArgManipulator
     */
    private $argManipulator;
    public function __construct(\Rector\DowngradePhp56\NodeManipulator\ArgManipulator $argManipulator)
    {
        $this->argManipulator = $argManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace argument unpacking by call_user_func_array()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(array $items)
    {
        some_function(...$items);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(array $items)
    {
        call_user_func_array('some_function', $items);
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
        return [\PhpParser\Node\Expr\CallLike::class];
    }
    /**
     * @param CallLike $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $args = $node->getArgs();
        if ($this->shouldSkip($args)) {
            return null;
        }
        if ($node instanceof \PhpParser\Node\Expr\FuncCall) {
            return $this->createCallUserFuncArrayFuncCall($this->funcCallToCallbackArg($node), $args);
        }
        if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->createCallUserFuncArrayFuncCall($this->methodCallToCallbackArg($node), $args);
        }
        if ($node instanceof \PhpParser\Node\Expr\New_) {
            return $this->createReflectionInstantiation($node, $args);
        }
        if ($node instanceof \PhpParser\Node\Expr\StaticCall) {
            return $this->createCallUserFuncArrayFuncCall($this->staticCallToCallbackArg($node), $args);
        }
        return null;
    }
    /**
     * @param Arg[] $args
     */
    private function shouldSkip(array $args) : bool
    {
        return !$this->argManipulator->hasUnpackedArg($args);
    }
    private function funcCallToCallbackArg(\PhpParser\Node\Expr\FuncCall $funcCall) : \PhpParser\Node\Arg
    {
        $callback = $funcCall->name instanceof \PhpParser\Node\Name ? new \PhpParser\Node\Scalar\String_($funcCall->name->toString()) : $funcCall->name;
        return new \PhpParser\Node\Arg($callback);
    }
    private function methodCallToCallbackArg(\PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node\Arg
    {
        $object = $methodCall->var;
        $method = $methodCall->name instanceof \PhpParser\Node\Identifier ? new \PhpParser\Node\Scalar\String_($methodCall->name->toString()) : $methodCall->name;
        $array = new \PhpParser\Node\Expr\Array_([new \PhpParser\Node\Expr\ArrayItem($object), new \PhpParser\Node\Expr\ArrayItem($method)]);
        return new \PhpParser\Node\Arg($array);
    }
    private function staticCallToCallbackArg(\PhpParser\Node\Expr\StaticCall $staticCall) : \PhpParser\Node\Arg
    {
        if ($staticCall->class instanceof \PhpParser\Node\Name) {
            $class = $staticCall->class->isSpecialClassName() ? new \PhpParser\Node\Scalar\String_($staticCall->class->toString()) : new \PhpParser\Node\Expr\ClassConstFetch($staticCall->class, 'class');
        } else {
            $class = $staticCall->class;
        }
        $method = $staticCall->name instanceof \PhpParser\Node\Identifier ? new \PhpParser\Node\Scalar\String_($staticCall->name->toString()) : $staticCall->name;
        $array = new \PhpParser\Node\Expr\Array_([new \PhpParser\Node\Expr\ArrayItem($class), new \PhpParser\Node\Expr\ArrayItem($method)]);
        return new \PhpParser\Node\Arg($array);
    }
    /**
     * @param Arg[] $args
     */
    private function createCallUserFuncArrayFuncCall(\PhpParser\Node\Arg $arg, array $args) : \PhpParser\Node\Expr\FuncCall
    {
        return new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('call_user_func_array'), [$arg, $this->mergeArgs($args)]);
    }
    /**
     * @param Arg[] $args
     */
    private function mergeArgs(array $args) : \PhpParser\Node\Arg
    {
        $unpackedArgs = $this->argManipulator->unpack($args);
        if (\count($unpackedArgs) === 1) {
            return $unpackedArgs[0];
        }
        return new \PhpParser\Node\Arg(new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('array_merge'), $unpackedArgs));
    }
    /**
     * @param Arg[] $args
     */
    private function createReflectionInstantiation(\PhpParser\Node\Expr\New_ $new, array $args) : ?\PhpParser\Node\Expr
    {
        if ($this->argManipulator->canBeInlined($args)) {
            $unpackedArgs = $this->argManipulator->unpack($args);
            \RectorPrefix20220501\Webmozart\Assert\Assert::minCount($unpackedArgs, 1);
            /** @var Array_ $array */
            $array = $unpackedArgs[0]->value;
            $arrayItems = \array_filter($array->items);
            $new->args = \array_map(function (\PhpParser\Node\Expr\ArrayItem $item) : Arg {
                return new \PhpParser\Node\Arg($item->value);
            }, $arrayItems);
            return $new;
        }
        if ($new->class instanceof \PhpParser\Node\Name) {
            switch (\strtolower($new->class->toString())) {
                case 'self':
                    $class = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('get_class'));
                    break;
                case 'static':
                    $class = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('get_called_class'));
                    break;
                case 'parent':
                    $class = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('get_parent_class'));
                    break;
                default:
                    $class = new \PhpParser\Node\Expr\ClassConstFetch($new->class, 'class');
                    break;
            }
        } elseif ($new->class instanceof \PhpParser\Node\Expr) {
            $class = $new->class;
        } else {
            return null;
        }
        $newReflection = new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name\FullyQualified('ReflectionClass'), [new \PhpParser\Node\Arg($class)]);
        return new \PhpParser\Node\Expr\MethodCall($newReflection, 'newInstanceArgs', [$this->mergeArgs($args)]);
    }
}
