<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArgPlaceholder;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\VariadicPlaceholder;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\New_\NarrowPreviousExceptionCodeArgRector\NarrowPreviousExceptionCodeArgRectorTest
 */
final class NarrowPreviousExceptionCodeArgRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @var int
     */
    private const CODE_POSITION = 1;
    /**
     * @var int
     */
    private const PREVIOUS_POSITION = 2;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove code of previous exception from new exception, as previous exception already carries it', [new CodeSample(<<<'CODE_SAMPLE'
try {
    $someCode = 1;
} catch (Throwable $throwable) {
    throw new \RuntimeException('ups', $throwable->getCode(), $throwable);
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
try {
    $someCode = 1;
} catch (Throwable $throwable) {
    throw new \RuntimeException('ups', previous: $throwable);
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [New_::class];
    }
    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$node->class instanceof Name) {
            return null;
        }
        $args = $node->getArgs();
        if (count($args) !== 3) {
            return null;
        }
        $codeArg = $node->getArg('code', self::CODE_POSITION);
        $previousArg = $node->getArg('previous', self::PREVIOUS_POSITION);
        if (!$codeArg instanceof Arg || !$previousArg instanceof Arg) {
            return null;
        }
        if (!$previousArg->value instanceof Variable) {
            return null;
        }
        if (!$codeArg->value instanceof MethodCall || !$this->isName($codeArg->value->name, 'getCode')) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($codeArg->value->var, $previousArg->value)) {
            return null;
        }
        if (!$this->isObjectType($node->class, new ObjectType('Throwable'))) {
            return null;
        }
        if (!$this->hasCodeAndPreviousParameterPositions($node->class)) {
            return null;
        }
        $node->args = array_values(array_filter($node->args, fn($arg): bool => $arg !== $codeArg));
        $previousArg->name = new Identifier('previous');
        return $node;
    }
    private function hasCodeAndPreviousParameterPositions(Name $name): bool
    {
        $className = $this->getName($name);
        if (!$this->reflectionProvider->hasClass($className)) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if (!$classReflection->hasConstructor()) {
            return \false;
        }
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($classReflection->getConstructor()->getVariants());
        $parameters = $extendedParametersAcceptor->getParameters();
        if (!isset($parameters[self::CODE_POSITION], $parameters[self::PREVIOUS_POSITION])) {
            return \false;
        }
        if ($parameters[self::CODE_POSITION]->getName() !== 'code') {
            return \false;
        }
        return $parameters[self::PREVIOUS_POSITION]->getName() === 'previous';
    }
}
