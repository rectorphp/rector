<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\Transform\NodeAnalyzer\FuncCallStaticCallToMethodCallAnalyzer;
use Rector\Transform\ValueObject\StaticCallToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202411\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\StaticCall\StaticCallToMethodCallRector\StaticCallToMethodCallRectorTest
 */
final class StaticCallToMethodCallRector extends AbstractScopeAwareRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\Transform\NodeAnalyzer\FuncCallStaticCallToMethodCallAnalyzer
     */
    private $funcCallStaticCallToMethodCallAnalyzer;
    /**
     * @var StaticCallToMethodCall[]
     */
    private $staticCallsToMethodCalls = [];
    public function __construct(FuncCallStaticCallToMethodCallAnalyzer $funcCallStaticCallToMethodCallAnalyzer)
    {
        $this->funcCallStaticCallToMethodCallAnalyzer = $funcCallStaticCallToMethodCallAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change static call to service method via constructor injection', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
use App\Custom\SmartFileSystem;

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
, [new StaticCallToMethodCall('Nette\\Utils\\FileSystem', 'write', 'App\\Custom\\SmartFileSystem', 'dumpFile')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        $class = $node;
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            $this->traverseNodesWithCallable($classMethod, function (Node $node) use($class, $classMethod, &$hasChanged) {
                if (!$node instanceof StaticCall) {
                    return null;
                }
                foreach ($this->staticCallsToMethodCalls as $staticCallToMethodCall) {
                    if (!$staticCallToMethodCall->isStaticCallMatch($node)) {
                        continue;
                    }
                    if ($classMethod->isStatic()) {
                        return $this->refactorToInstanceCall($node, $staticCallToMethodCall);
                    }
                    $expr = $this->funcCallStaticCallToMethodCallAnalyzer->matchTypeProvidingExpr($class, $classMethod, $staticCallToMethodCall->getClassObjectType());
                    if ($staticCallToMethodCall->getMethodName() === '*') {
                        $methodName = $this->getName($node->name);
                    } else {
                        $methodName = $staticCallToMethodCall->getMethodName();
                    }
                    if (!\is_string($methodName)) {
                        throw new ShouldNotHappenException();
                    }
                    $hasChanged = \true;
                    return new MethodCall($expr, $methodName, $node->args);
                }
                return $node;
            });
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, StaticCallToMethodCall::class);
        $this->staticCallsToMethodCalls = $configuration;
    }
    private function refactorToInstanceCall(StaticCall $staticCall, StaticCallToMethodCall $staticCallToMethodCall) : MethodCall
    {
        $new = new New_(new FullyQualified($staticCallToMethodCall->getClassType()));
        return new MethodCall($new, $staticCallToMethodCall->getMethodName(), $staticCall->args);
    }
}
