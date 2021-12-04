<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Catch_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\NodeTraverser;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/thecodingmachine/phpstan-strict-rules/blob/e3d746a61d38993ca2bc2e2fcda7012150de120c/src/Rules/Exceptions/ThrowMustBundlePreviousExceptionRule.php#L83
 * @see \Rector\Tests\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector\ThrowWithPreviousExceptionRectorTest
 */
final class ThrowWithPreviousExceptionRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var int
     */
    private const DEFAULT_EXCEPTION_ARGUMENT_POSITION = 2;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('When throwing into a catch block, checks that the previous exception is passed to the new throw clause', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        try {
            $someCode = 1;
        } catch (Throwable $throwable) {
            throw new AnotherException('ups');
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        try {
            $someCode = 1;
        } catch (Throwable $throwable) {
            throw new AnotherException('ups', $throwable->getCode(), $throwable);
        }
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
        return [\PhpParser\Node\Stmt\Catch_::class];
    }
    /**
     * @param Catch_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $caughtThrowableVariable = $node->var;
        if (!$caughtThrowableVariable instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        $this->traverseNodesWithCallable($node->stmts, function (\PhpParser\Node $node) use($caughtThrowableVariable) : ?int {
            if (!$node instanceof \PhpParser\Node\Stmt\Throw_) {
                return null;
            }
            return $this->refactorThrow($node, $caughtThrowableVariable);
        });
        return $node;
    }
    private function refactorThrow(\PhpParser\Node\Stmt\Throw_ $throw, \PhpParser\Node\Expr\Variable $catchedThrowableVariable) : ?int
    {
        if (!$throw->expr instanceof \PhpParser\Node\Expr\New_) {
            return null;
        }
        $new = $throw->expr;
        if (!$new->class instanceof \PhpParser\Node\Name) {
            return null;
        }
        $exceptionArgumentPosition = $this->resolveExceptionArgumentPosition($new->class);
        if ($exceptionArgumentPosition === null) {
            return null;
        }
        // exception is bundled
        if (isset($new->args[$exceptionArgumentPosition])) {
            return null;
        }
        if (!isset($new->args[0])) {
            // get previous message
            $new->args[0] = new \PhpParser\Node\Arg(new \PhpParser\Node\Expr\MethodCall($catchedThrowableVariable, 'getMessage'));
        }
        if (!isset($new->args[1])) {
            // get previous code
            $new->args[1] = new \PhpParser\Node\Arg(new \PhpParser\Node\Expr\MethodCall($catchedThrowableVariable, 'getCode'));
        }
        /** @var Arg $arg1 */
        $arg1 = $new->args[1];
        if ($arg1->name instanceof \PhpParser\Node\Identifier && $arg1->name->toString() === 'previous') {
            $new->args[1] = new \PhpParser\Node\Arg(new \PhpParser\Node\Expr\MethodCall($catchedThrowableVariable, 'getCode'));
            $new->args[$exceptionArgumentPosition] = $arg1;
        } else {
            $new->args[$exceptionArgumentPosition] = new \PhpParser\Node\Arg($catchedThrowableVariable);
        }
        // null the node, to fix broken format preserving printers, see https://github.com/rectorphp/rector/issues/5576
        $new->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        // nothing more to add
        return \PhpParser\NodeTraverser::DONT_TRAVERSE_CHILDREN;
    }
    private function resolveExceptionArgumentPosition(\PhpParser\Node\Name $exceptionName) : ?int
    {
        $className = $this->getName($exceptionName);
        // is native exception?
        if (\strpos($className, '\\') === \false) {
            return self::DEFAULT_EXCEPTION_ARGUMENT_POSITION;
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return self::DEFAULT_EXCEPTION_ARGUMENT_POSITION;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $construct = $classReflection->hasMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$construct) {
            return self::DEFAULT_EXCEPTION_ARGUMENT_POSITION;
        }
        $constructorReflectionMethod = $classReflection->getConstructor();
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($constructorReflectionMethod->getVariants());
        foreach ($parametersAcceptor->getParameters() as $position => $parameterReflection) {
            $parameterType = $parameterReflection->getType();
            if (!$parameterType instanceof \PHPStan\Type\TypeWithClassName) {
                continue;
            }
            $objectType = new \PHPStan\Type\ObjectType('Throwable');
            if ($objectType->isSuperTypeOf($parameterType)->no()) {
                continue;
            }
            return $position;
        }
        return null;
    }
}
