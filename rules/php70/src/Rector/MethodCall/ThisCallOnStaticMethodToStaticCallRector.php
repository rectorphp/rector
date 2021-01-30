<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeCollector\Reflection\MethodReflectionProvider;
use Rector\NodeCollector\StaticAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionMethod;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://3v4l.org/rkiSC
 * @see \Rector\Php70\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\ThisCallOnStaticMethodToStaticCallRectorTest
 */
final class ThisCallOnStaticMethodToStaticCallRector extends AbstractRector
{
    /**
     * @var StaticAnalyzer
     */
    private $staticAnalyzer;

    /**
     * @var MethodReflectionProvider
     */
    private $methodReflectionProvider;

    public function __construct(StaticAnalyzer $staticAnalyzer, MethodReflectionProvider $methodReflectionProvider)
    {
        $this->staticAnalyzer = $staticAnalyzer;
        $this->methodReflectionProvider = $methodReflectionProvider;
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

            ]);
    }

    /**
     * @return string[]
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
        if (! $this->isVariableName($node->var, 'this')) {
            return null;
        }

        $methodName = $this->getName($node->name);
        if ($methodName === null) {
            return null;
        }

        // skip PHPUnit calls, as they accept both self:: and $this-> formats
        if ($this->isObjectType($node->var, 'PHPUnit\Framework\TestCase')) {
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

        $methodReflection = $this->methodReflectionProvider->provideByMethodCall($methodCall);
        if ($methodReflection instanceof ReflectionMethod && $methodReflection->isPrivate()) {
            return 'self';
        }

        return 'static';
    }
}
