<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Catch_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\NodeVisitor;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector\ThrowWithPreviousExceptionRectorTest
 */
final class ThrowWithPreviousExceptionRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @var int
     */
    private const DEFAULT_EXCEPTION_ARGUMENT_POSITION = 2;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('When throwing into a catch block, checks that the previous exception is passed to the new throw clause', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Catch_::class];
    }
    /**
     * @param Catch_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $caughtThrowableVariable = $node->var;
        if (!$caughtThrowableVariable instanceof Variable) {
            return null;
        }
        $isChanged = \false;
        $this->traverseNodesWithCallable($node->stmts, function (Node $node) use($caughtThrowableVariable, &$isChanged) : ?int {
            if (!$node instanceof Throw_) {
                return null;
            }
            $isChanged = $this->refactorThrow($node, $caughtThrowableVariable);
            return $isChanged;
        });
        if (!(bool) $isChanged) {
            return null;
        }
        return $node;
    }
    private function refactorThrow(Throw_ $throw, Variable $caughtThrowableVariable) : ?int
    {
        if (!$throw->expr instanceof New_) {
            return null;
        }
        $new = $throw->expr;
        if (!$new->class instanceof Name) {
            return null;
        }
        $exceptionArgumentPosition = $this->resolveExceptionArgumentPosition($new->class);
        if ($exceptionArgumentPosition === null) {
            return null;
        }
        if ($new->isFirstClassCallable()) {
            return null;
        }
        // exception is bundled
        if (isset($new->getArgs()[$exceptionArgumentPosition])) {
            return null;
        }
        /** @var Arg|null $messageArgument */
        $messageArgument = $new->args[0] ?? null;
        $shouldUseNamedArguments = $messageArgument instanceof Arg && $messageArgument->name instanceof Identifier;
        if (!isset($new->args[0])) {
            // get previous message
            $getMessageMethodCall = new MethodCall($caughtThrowableVariable, 'getMessage');
            $new->args[0] = new Arg($getMessageMethodCall);
        } elseif ($new->args[0] instanceof Arg && $new->args[0]->name instanceof Identifier && $new->args[0]->name->toString() === 'previous' && $this->nodeComparator->areNodesEqual($new->args[0]->value, $caughtThrowableVariable)) {
            $new->args[0]->name->name = 'message';
            $new->args[0]->value = new MethodCall($caughtThrowableVariable, 'getMessage');
        }
        if (!isset($new->getArgs()[1])) {
            // get previous code
            $new->args[1] = new Arg(new MethodCall($caughtThrowableVariable, 'getCode'), \false, \false, [], $shouldUseNamedArguments ? new Identifier('code') : null);
        }
        /** @var Arg $arg1 */
        $arg1 = $new->args[1];
        if ($arg1->name instanceof Identifier && $arg1->name->toString() === 'previous') {
            $new->args[1] = new Arg(new MethodCall($caughtThrowableVariable, 'getCode'), \false, \false, [], $shouldUseNamedArguments ? new Identifier('code') : null);
            $new->args[$exceptionArgumentPosition] = $arg1;
        } else {
            $new->args[$exceptionArgumentPosition] = new Arg($caughtThrowableVariable, \false, \false, [], $shouldUseNamedArguments ? new Identifier('previous') : null);
        }
        // null the node, to fix broken format preserving printers, see https://github.com/rectorphp/rector/issues/5576
        $new->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        // nothing more to add
        return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
    }
    private function resolveExceptionArgumentPosition(Name $exceptionName) : ?int
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
        $construct = $classReflection->hasMethod(MethodName::CONSTRUCT);
        if (!$construct) {
            return self::DEFAULT_EXCEPTION_ARGUMENT_POSITION;
        }
        $extendedMethodReflection = $classReflection->getConstructor();
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($extendedMethodReflection->getVariants());
        foreach ($extendedParametersAcceptor->getParameters() as $position => $parameterReflectionWithPhpDoc) {
            $parameterType = $parameterReflectionWithPhpDoc->getType();
            $className = ClassNameFromObjectTypeResolver::resolve($parameterReflectionWithPhpDoc->getType());
            if ($className === null) {
                continue;
            }
            $objectType = new ObjectType('Throwable');
            if ($objectType->isSuperTypeOf($parameterType)->no()) {
                continue;
            }
            return $position;
        }
        return null;
    }
}
