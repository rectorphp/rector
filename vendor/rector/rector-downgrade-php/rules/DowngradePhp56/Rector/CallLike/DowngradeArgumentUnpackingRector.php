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
use RectorPrefix202211\Webmozart\Assert\Assert;
/**
 * @changelog https://wiki.php.net/rfc/argument_unpacking
 *
 * @see \Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\DowngradeArgumentUnpackingRectorTest
 */
final class DowngradeArgumentUnpackingRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DowngradePhp56\NodeManipulator\ArgManipulator
     */
    private $argManipulator;
    public function __construct(ArgManipulator $argManipulator)
    {
        $this->argManipulator = $argManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace argument unpacking by call_user_func_array()', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [CallLike::class];
    }
    /**
     * @param CallLike $node
     */
    public function refactor(Node $node) : ?Node
    {
        $args = $node->getArgs();
        if ($this->shouldSkip($args)) {
            return null;
        }
        if ($node instanceof FuncCall) {
            return $this->createCallUserFuncArrayFuncCall($this->funcCallToCallbackArg($node), $args);
        }
        if ($node instanceof MethodCall) {
            return $this->createCallUserFuncArrayFuncCall($this->methodCallToCallbackArg($node), $args);
        }
        if ($node instanceof New_) {
            return $this->createReflectionInstantiation($node, $args);
        }
        if ($node instanceof StaticCall) {
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
    private function funcCallToCallbackArg(FuncCall $funcCall) : Arg
    {
        $callback = $funcCall->name instanceof Name ? new String_($funcCall->name->toString()) : $funcCall->name;
        return new Arg($callback);
    }
    private function methodCallToCallbackArg(MethodCall $methodCall) : Arg
    {
        $object = $methodCall->var;
        $method = $methodCall->name instanceof Identifier ? new String_($methodCall->name->toString()) : $methodCall->name;
        $array = new Array_([new ArrayItem($object), new ArrayItem($method)]);
        return new Arg($array);
    }
    private function staticCallToCallbackArg(StaticCall $staticCall) : Arg
    {
        if ($staticCall->class instanceof Name) {
            $class = $staticCall->class->isSpecialClassName() ? new String_($staticCall->class->toString()) : new ClassConstFetch($staticCall->class, 'class');
        } else {
            $class = $staticCall->class;
        }
        $method = $staticCall->name instanceof Identifier ? new String_($staticCall->name->toString()) : $staticCall->name;
        $array = new Array_([new ArrayItem($class), new ArrayItem($method)]);
        return new Arg($array);
    }
    /**
     * @param Arg[] $args
     */
    private function createCallUserFuncArrayFuncCall(Arg $arg, array $args) : FuncCall
    {
        return new FuncCall(new Name('call_user_func_array'), [$arg, $this->mergeArgs($args)]);
    }
    /**
     * @param Arg[] $args
     */
    private function mergeArgs(array $args) : Arg
    {
        $unpackedArgs = $this->argManipulator->unpack($args);
        if (\count($unpackedArgs) === 1) {
            return $unpackedArgs[0];
        }
        return new Arg(new FuncCall(new Name('array_merge'), $unpackedArgs));
    }
    /**
     * @param Arg[] $args
     */
    private function createReflectionInstantiation(New_ $new, array $args) : ?Expr
    {
        if ($this->argManipulator->canBeInlined($args)) {
            $unpackedArgs = $this->argManipulator->unpack($args);
            Assert::minCount($unpackedArgs, 1);
            /** @var Array_ $array */
            $array = $unpackedArgs[0]->value;
            $arrayItems = \array_filter($array->items);
            $new->args = \array_map(static function (ArrayItem $arrayItem) : Arg {
                return new Arg($arrayItem->value);
            }, $arrayItems);
            return $new;
        }
        if ($new->class instanceof Name) {
            switch (\strtolower($new->class->toString())) {
                case 'self':
                    $class = new FuncCall(new Name('get_class'));
                    break;
                case 'static':
                    $class = new FuncCall(new Name('get_called_class'));
                    break;
                case 'parent':
                    $class = new FuncCall(new Name('get_parent_class'));
                    break;
                default:
                    $class = new ClassConstFetch($new->class, 'class');
                    break;
            }
        } elseif ($new->class instanceof Expr) {
            $class = $new->class;
        } else {
            return null;
        }
        $newReflection = new New_(new FullyQualified('ReflectionClass'), [new Arg($class)]);
        return new MethodCall($newReflection, 'newInstanceArgs', [$this->mergeArgs($args)]);
    }
}
