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
/**
 * @see \Rector\Tests\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector\ReplaceStringWithClassConstantRectorTest
 */
final class ReplaceStringWithClassConstantRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @deprecated
     * @var string
     */
    public const REPLACE_STRING_WITH_CLASS_CONSTANT = 'replace_string_with_class_constant';
    /**
     * @var ReplaceStringWithClassConstant[]
     */
    private $replaceStringWithClassConstants = [];
    /**
     * @readonly
     * @var \Rector\Privatization\NodeFactory\ClassConstantFetchValueFactory
     */
    private $classConstantFetchValueFactory;
    public function __construct(\Rector\Privatization\NodeFactory\ClassConstantFetchValueFactory $classConstantFetchValueFactory)
    {
        $this->classConstantFetchValueFactory = $classConstantFetchValueFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace string values in specific method call by constant of provided class', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, [new \Rector\Privatization\ValueObject\ReplaceStringWithClassConstant('SomeClass', 'call', 0, 'Placeholder')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->args === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($this->replaceStringWithClassConstants as $replaceStringWithClassConstant) {
            $desiredArg = $this->matchArg($node, $replaceStringWithClassConstant);
            if (!$desiredArg instanceof \PhpParser\Node\Arg) {
                continue;
            }
            $classConstFetch = $this->classConstantFetchValueFactory->create($desiredArg->value, $replaceStringWithClassConstant->getClassWithConstants(), $replaceStringWithClassConstant->isCaseInsensitive());
            if (!$classConstFetch instanceof \PhpParser\Node\Expr\ClassConstFetch) {
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
        $this->replaceStringWithClassConstants = $configuration[self::REPLACE_STRING_WITH_CLASS_CONSTANT] ?? $configuration;
    }
    private function matchArg(\PhpParser\Node\Expr\MethodCall $methodCall, \Rector\Privatization\ValueObject\ReplaceStringWithClassConstant $replaceStringWithClassConstant) : ?\PhpParser\Node\Arg
    {
        if (!$this->isObjectType($methodCall->var, $replaceStringWithClassConstant->getObjectType())) {
            return null;
        }
        if (!$this->isName($methodCall->name, $replaceStringWithClassConstant->getMethod())) {
            return null;
        }
        $desiredArg = $methodCall->args[$replaceStringWithClassConstant->getArgPosition()] ?? null;
        if (!$desiredArg instanceof \PhpParser\Node\Arg) {
            return null;
        }
        if ($desiredArg->value instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return null;
        }
        return $desiredArg;
    }
}
