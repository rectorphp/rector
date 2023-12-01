<?php

declare (strict_types=1);
namespace Rector\Arguments\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Arguments\ArgumentDefaultValueReplacer;
use Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202312\Webmozart\Assert\Assert;
/**
 * @api used in rector-symfony
 * @see \Rector\Tests\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector\ReplaceArgumentDefaultValueRectorTest
 */
final class ReplaceArgumentDefaultValueRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\Arguments\ArgumentDefaultValueReplacer
     */
    private $argumentDefaultValueReplacer;
    /**
     * @var ReplaceArgumentDefaultValue[]
     */
    private $replaceArgumentDefaultValues = [];
    public function __construct(ArgumentDefaultValueReplacer $argumentDefaultValueReplacer)
    {
        $this->argumentDefaultValueReplacer = $argumentDefaultValueReplacer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replaces defined map of arguments in defined methods and their calls.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod(SomeClass::OLD_CONSTANT);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod(false);
CODE_SAMPLE
, [new ReplaceArgumentDefaultValue('SomeClass', 'someMethod', 0, 'SomeClass::OLD_CONSTANT', \false)])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class, ClassMethod::class, New_::class];
    }
    /**
     * @param MethodCall|StaticCall|ClassMethod|New_ $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\New_|null
     */
    public function refactor(Node $node)
    {
        $hasChanged = \false;
        if ($node instanceof New_) {
            return $this->refactorNew($node);
        }
        $nodeName = $this->getName($node->name);
        if ($nodeName === null) {
            return null;
        }
        foreach ($this->replaceArgumentDefaultValues as $replaceArgumentDefaultValue) {
            if (!$this->nodeNameResolver->isStringName($nodeName, $replaceArgumentDefaultValue->getMethod())) {
                continue;
            }
            if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, $replaceArgumentDefaultValue->getObjectType())) {
                continue;
            }
            $replacedNode = $this->argumentDefaultValueReplacer->processReplaces($node, $replaceArgumentDefaultValue);
            if ($replacedNode instanceof Node) {
                $hasChanged = \true;
            }
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
        Assert::allIsAOf($configuration, ReplaceArgumentDefaultValue::class);
        $this->replaceArgumentDefaultValues = $configuration;
    }
    private function refactorNew(New_ $new) : ?New_
    {
        foreach ($this->replaceArgumentDefaultValues as $replaceArgumentDefaultValue) {
            if ($replaceArgumentDefaultValue->getMethod() !== MethodName::CONSTRUCT) {
                continue;
            }
            if (!$this->isObjectType($new, $replaceArgumentDefaultValue->getObjectType())) {
                continue;
            }
            return $this->argumentDefaultValueReplacer->processReplaces($new, $replaceArgumentDefaultValue);
        }
        return null;
    }
}
