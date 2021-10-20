<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Transform\NodeAnalyzer\FuncCallStaticCallToMethodCallAnalyzer;
use Rector\Transform\ValueObject\StaticCallToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\StaticCall\StaticCallToMethodCallRector\StaticCallToMethodCallRectorTest
 */
final class StaticCallToMethodCallRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
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
    /**
     * @var \Rector\Transform\NodeAnalyzer\FuncCallStaticCallToMethodCallAnalyzer
     */
    private $funcCallStaticCallToMethodCallAnalyzer;
    public function __construct(\Rector\Transform\NodeAnalyzer\FuncCallStaticCallToMethodCallAnalyzer $funcCallStaticCallToMethodCallAnalyzer)
    {
        $this->funcCallStaticCallToMethodCallAnalyzer = $funcCallStaticCallToMethodCallAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change static call to service method via constructor injection', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
use Nette\Utils\FileSystem;

class SomeClass
{
    public function run()
    {
        return FileSystem::write('file', 'content');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
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
, [self::STATIC_CALLS_TO_METHOD_CALLS => [new \Rector\Transform\ValueObject\StaticCallToMethodCall('Nette\\Utils\\FileSystem', 'write', 'Symplify\\SmartFileSystem\\SmartFileSystem', 'dumpFile')]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $classLike = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        $classMethod = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return null;
        }
        foreach ($this->staticCallsToMethodCalls as $staticCallToMethodCall) {
            if (!$staticCallToMethodCall->isStaticCallMatch($node)) {
                continue;
            }
            if ($classMethod->isStatic()) {
                return $this->refactorToInstanceCall($node, $staticCallToMethodCall);
            }
            $expr = $this->funcCallStaticCallToMethodCallAnalyzer->matchTypeProvidingExpr($classLike, $classMethod, $staticCallToMethodCall->getClassObjectType());
            if ($staticCallToMethodCall->getMethodName() === '*') {
                $methodName = $this->getName($node->name);
            } else {
                $methodName = $staticCallToMethodCall->getMethodName();
            }
            if (!\is_string($methodName)) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            return new \PhpParser\Node\Expr\MethodCall($expr, $methodName, $node->args);
        }
        return $node;
    }
    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration) : void
    {
        $staticCallsToMethodCalls = $configuration[self::STATIC_CALLS_TO_METHOD_CALLS] ?? [];
        \RectorPrefix20211020\Webmozart\Assert\Assert::allIsInstanceOf($staticCallsToMethodCalls, \Rector\Transform\ValueObject\StaticCallToMethodCall::class);
        $this->staticCallsToMethodCalls = $staticCallsToMethodCalls;
    }
    private function refactorToInstanceCall(\PhpParser\Node\Expr\StaticCall $staticCall, \Rector\Transform\ValueObject\StaticCallToMethodCall $staticCallToMethodCall) : \PhpParser\Node\Expr\MethodCall
    {
        $new = new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name\FullyQualified($staticCallToMethodCall->getClassType()));
        return new \PhpParser\Node\Expr\MethodCall($new, $staticCallToMethodCall->getMethodName(), $staticCall->args);
    }
}
