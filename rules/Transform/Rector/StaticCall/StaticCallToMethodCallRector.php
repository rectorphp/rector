<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\Transform\NodeAnalyzer\FuncCallStaticCallToMethodCallAnalyzer;
use Rector\Transform\ValueObject\StaticCallToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202506\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\StaticCall\StaticCallToMethodCallRector\StaticCallToMethodCallRectorTest
 */
final class StaticCallToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     */
    private FuncCallStaticCallToMethodCallAnalyzer $funcCallStaticCallToMethodCallAnalyzer;
    /**
     * @var StaticCallToMethodCall[]
     */
    private array $staticCallsToMethodCalls = [];
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
    public function refactor(Node $node) : ?Node
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
                    $methodName = $this->getMethodName($node, $staticCallToMethodCall);
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
    private function getMethodName(StaticCall $staticCall, StaticCallToMethodCall $staticCallToMethodCall) : string
    {
        if ($staticCallToMethodCall->getMethodName() === '*') {
            $methodName = $this->getName($staticCall->name);
        } else {
            $methodName = $staticCallToMethodCall->getMethodName();
        }
        if (!\is_string($methodName)) {
            throw new ShouldNotHappenException();
        }
        return $methodName;
    }
    private function refactorToInstanceCall(StaticCall $staticCall, StaticCallToMethodCall $staticCallToMethodCall) : MethodCall
    {
        $new = new New_(new FullyQualified($staticCallToMethodCall->getClassType()));
        $methodName = $this->getMethodName($staticCall, $staticCallToMethodCall);
        return new MethodCall($new, $methodName, $staticCall->args);
    }
}
