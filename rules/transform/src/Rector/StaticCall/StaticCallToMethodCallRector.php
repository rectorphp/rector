<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Transform\Rector\AbstractToMethodCallRector;
use Rector\Transform\ValueObject\StaticCallToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Transform\Tests\Rector\StaticCall\StaticCallToMethodCallRector\StaticCallToMethodCallRectorTest
 */
final class StaticCallToMethodCallRector extends AbstractToMethodCallRector
{
    /**
     * @api
     * @var string
     */
    public const STATIC_CALLS_TO_METHOD_CALLS = 'static_calls_to_method_calls';

    /**
     * @var StaticCallToMethodCall[]
     */
    private $staticCallsToMethodCalls = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change static call to service method via constructor injection', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
use Nette\Utils\FileSystem;

class SomeClass
{
    public function run()
    {
        return FileSystem::write('file', 'content');
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
use Symplify\SmartFileSystem\SmartFileSystem;

class SomeClass
{
    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(SmartFileSystem $smartFileSystem)
    {
        $this->smartFileSystem = $smartFileSystem;
    }

    public function run()
    {
        return $this->smartFileSystem->dumpFile('file', 'content');
    }
}
CODE_SAMPLE
            , [
                self::STATIC_CALLS_TO_METHOD_CALLS => [
                    new StaticCallToMethodCall(
                        'Nette\Utils\FileSystem',
                        'write',
                        'Symplify\SmartFileSystem\SmartFileSystem',
                        'dumpFile'
                    ),
                ],
            ]),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return null;
        }

        $classMethod = $node->getAttribute(AttributeKey::METHOD_NODE);
        if (! $classMethod instanceof ClassMethod) {
            return null;
        }

        foreach ($this->staticCallsToMethodCalls as $staticCallToMethodCall) {
            if (! $staticCallToMethodCall->isStaticCallMatch($node)) {
                continue;
            }

            if ($classMethod->isStatic()) {
                return $this->refactorToInstanceCall($node, $staticCallToMethodCall);
            }

            $expr = $this->matchTypeProvidingExpr($classLike, $classMethod, $staticCallToMethodCall->getClassType());

            if ($staticCallToMethodCall->getMethodName() === '*') {
                $methodName = $this->getName($node->name);
            } else {
                $methodName = $staticCallToMethodCall->getMethodName();
            }

            if (! is_string($methodName)) {
                throw new ShouldNotHappenException();
            }

            return new MethodCall($expr, $methodName, $node->args);
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $staticCallsToMethodCalls = $configuration[self::STATIC_CALLS_TO_METHOD_CALLS] ?? [];
        Assert::allIsInstanceOf($staticCallsToMethodCalls, StaticCallToMethodCall::class);
        $this->staticCallsToMethodCalls = $staticCallsToMethodCalls;
    }

    private function refactorToInstanceCall(
        StaticCall $staticCall,
        StaticCallToMethodCall $staticCallToMethodCall
    ): MethodCall {
        $new = new New_(new FullyQualified($staticCallToMethodCall->getClassType()));
        return new MethodCall($new, $staticCallToMethodCall->getMethodName(), $staticCall->args);
    }
}
