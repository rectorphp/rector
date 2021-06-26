<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeCollector\StaticAnalyzer;
use Rector\NodeTypeResolver\MethodParameterTypeResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://3v4l.org/rkiSC
 * @see \Rector\Tests\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\ThisCallOnStaticMethodToStaticCallRectorTest
 */
final class ThisCallOnStaticMethodToStaticCallRector extends AbstractRector
{
    public function __construct(
        private StaticAnalyzer $staticAnalyzer,
        private MethodParameterTypeResolver $methodReflectionProvider,
        private ReflectionResolver $reflectionResolver
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes $this->call() to static method to static call',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public static function run()
    {
        $this->eat();
    }

    public static function eat()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public static function run()
    {
        static::eat();
    }

    public static function eat()
    {
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->var instanceof Variable) {
            return null;
        }

        if (! $this->nodeNameResolver->isName($node->var, 'this')) {
            return null;
        }

        $methodName = $this->getName($node->name);
        if ($methodName === null) {
            return null;
        }

        // skip PHPUnit calls, as they accept both self:: and $this-> formats
        if ($this->isObjectType($node->var, new ObjectType('PHPUnit\Framework\TestCase'))) {
            return null;
        }

        /** @var class-string $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        if (! is_string($className)) {
            return null;
        }

        $isStaticMethod = $this->staticAnalyzer->isStaticMethod($methodName, $className);
        if (! $isStaticMethod) {
            return null;
        }

        $classReference = $this->resolveClassSelf($node);
        return $this->nodeFactory->createStaticCall($classReference, $methodName, $node->args);
    }

    private function resolveClassSelf(MethodCall $methodCall): string
    {
        $classLike = $methodCall->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return 'static';
        }

        if ($classLike->isFinal()) {
            return 'self';
        }

        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromMethodCall($methodCall);
        if (! $methodReflection instanceof PhpMethodReflection) {
            return 'static';
        }

        if (! $methodReflection->isPrivate()) {
            return 'static';
        }

        return 'self';
    }
}
