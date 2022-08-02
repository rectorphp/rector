<?php

declare (strict_types=1);
namespace Rector\Privatization\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Privatization\NodeFactory\ClassConstantFetchValueFactory;
use Rector\Privatization\ValueObject\ReplaceStringWithClassConstant;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202208\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector\ReplaceStringWithClassConstantRectorTest
 */
final class ReplaceStringWithClassConstantRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var ReplaceStringWithClassConstant[]
     */
    private $replaceStringWithClassConstants = [];
    /**
     * @readonly
     * @var \Rector\Privatization\NodeFactory\ClassConstantFetchValueFactory
     */
    private $classConstantFetchValueFactory;
    public function __construct(ClassConstantFetchValueFactory $classConstantFetchValueFactory)
    {
        $this->classConstantFetchValueFactory = $classConstantFetchValueFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace string values in specific method call by constant of provided class', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->call('name');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->call(Placeholder::NAME);
    }
}
CODE_SAMPLE
, [new ReplaceStringWithClassConstant('SomeClass', 'call', 0, 'Placeholder')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->args === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($this->replaceStringWithClassConstants as $replaceStringWithClassConstant) {
            $desiredArg = $this->matchArg($node, $replaceStringWithClassConstant);
            if (!$desiredArg instanceof Arg) {
                continue;
            }
            $classConstFetch = $this->classConstantFetchValueFactory->create($desiredArg->value, $replaceStringWithClassConstant->getClassWithConstants(), $replaceStringWithClassConstant->isCaseInsensitive());
            if (!$classConstFetch instanceof ClassConstFetch) {
                continue;
            }
            $desiredArg->value = $classConstFetch;
            $hasChanged = \true;
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
        Assert::allIsAOf($configuration, ReplaceStringWithClassConstant::class);
        $this->replaceStringWithClassConstants = $configuration;
    }
    private function matchArg(MethodCall $methodCall, ReplaceStringWithClassConstant $replaceStringWithClassConstant) : ?Arg
    {
        if (!$this->isObjectType($methodCall->var, $replaceStringWithClassConstant->getObjectType())) {
            return null;
        }
        if (!$this->isName($methodCall->name, $replaceStringWithClassConstant->getMethod())) {
            return null;
        }
        $desiredArg = $methodCall->args[$replaceStringWithClassConstant->getArgPosition()] ?? null;
        if (!$desiredArg instanceof Arg) {
            return null;
        }
        if ($desiredArg->value instanceof ClassConstFetch) {
            return null;
        }
        return $desiredArg;
    }
}
