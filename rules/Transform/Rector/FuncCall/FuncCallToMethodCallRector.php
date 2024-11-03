<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Class_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\Transform\NodeAnalyzer\FuncCallStaticCallToMethodCallAnalyzer;
use Rector\Transform\ValueObject\FuncCallToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202411\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\FuncCall\FuncCallToMethodCallRector\FuncCallToMethodCallRectorTest
 */
final class FuncCallToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\Transform\NodeAnalyzer\FuncCallStaticCallToMethodCallAnalyzer
     */
    private $funcCallStaticCallToMethodCallAnalyzer;
    /**
     * @var FuncCallToMethodCall[]
     */
    private $funcNameToMethodCallNames = [];
    public function __construct(FuncCallStaticCallToMethodCallAnalyzer $funcCallStaticCallToMethodCallAnalyzer)
    {
        $this->funcCallStaticCallToMethodCallAnalyzer = $funcCallStaticCallToMethodCallAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns defined function calls to local method calls.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        view('...');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var \Namespaced\SomeRenderer
     */
    private $someRenderer;

    public function __construct(\Namespaced\SomeRenderer $someRenderer)
    {
        $this->someRenderer = $someRenderer;
    }

    public function run()
    {
        $this->someRenderer->view('...');
    }
}
CODE_SAMPLE
, [new FuncCallToMethodCall('view', 'Namespaced\\SomeRenderer', 'render')])]);
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
        $hasChanged = \false;
        $class = $node;
        foreach ($node->getMethods() as $classMethod) {
            if ($classMethod->isStatic()) {
                continue;
            }
            if ($classMethod->isAbstract()) {
                continue;
            }
            $this->traverseNodesWithCallable($classMethod, function (Node $node) use($class, $classMethod, &$hasChanged) : ?Node {
                if (!$node instanceof FuncCall) {
                    return null;
                }
                foreach ($this->funcNameToMethodCallNames as $funcNameToMethodCallName) {
                    if (!$this->isName($node->name, $funcNameToMethodCallName->getOldFuncName())) {
                        continue;
                    }
                    $expr = $this->funcCallStaticCallToMethodCallAnalyzer->matchTypeProvidingExpr($class, $classMethod, $funcNameToMethodCallName->getNewObjectType());
                    $hasChanged = \true;
                    return $this->nodeFactory->createMethodCall($expr, $funcNameToMethodCallName->getNewMethodName(), $node->args);
                }
                return null;
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
        Assert::allIsAOf($configuration, FuncCallToMethodCall::class);
        $this->funcNameToMethodCallNames = $configuration;
    }
}
