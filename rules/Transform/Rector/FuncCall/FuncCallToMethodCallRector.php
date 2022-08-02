<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\NodeAnalyzer\FuncCallStaticCallToMethodCallAnalyzer;
use Rector\Transform\ValueObject\FuncCallToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202208\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\FuncCall\FuncCallToMethodCallRector\FuncCallToMethodCallRectorTest
 */
final class FuncCallToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var FuncCallToMethodCall[]
     */
    private $funcNameToMethodCallNames = [];
    /**
     * @readonly
     * @var \Rector\Transform\NodeAnalyzer\FuncCallStaticCallToMethodCallAnalyzer
     */
    private $funcCallStaticCallToMethodCallAnalyzer;
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        $classLike = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$classLike instanceof Class_) {
            return null;
        }
        $classMethod = $this->betterNodeFinder->findParentType($node, ClassMethod::class);
        if (!$classMethod instanceof ClassMethod) {
            return null;
        }
        if ($classMethod->isStatic()) {
            return null;
        }
        foreach ($this->funcNameToMethodCallNames as $funcNameToMethodCallName) {
            if (!$this->isName($node->name, $funcNameToMethodCallName->getOldFuncName())) {
                continue;
            }
            $expr = $this->funcCallStaticCallToMethodCallAnalyzer->matchTypeProvidingExpr($classLike, $classMethod, $funcNameToMethodCallName->getNewObjectType());
            return $this->nodeFactory->createMethodCall($expr, $funcNameToMethodCallName->getNewMethodName(), $node->args);
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
