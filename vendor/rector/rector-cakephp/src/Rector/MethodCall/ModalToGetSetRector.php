<?php

declare (strict_types=1);
namespace Rector\CakePHP\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\CakePHP\ValueObject\ModalToGetSet;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202208\Webmozart\Assert\Assert;
/**
 * @see https://book.cakephp.org/3.0/en/appendices/3-4-migration-guide.html#deprecated-combined-get-set-methods
 * @see https://github.com/cakephp/cakephp/commit/326292688c5e6d08945a3cafa4b6ffb33e714eea#diff-e7c0f0d636ca50a0350e9be316d8b0f9
 *
 * @see \Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\ModalToGetSetRectorTest
 */
final class ModalToGetSetRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const UNPREFIXED_METHODS_TO_GET_SET = 'unprefixed_methods_to_get_set';
    /**
     * @var ModalToGetSet[]
     */
    private $unprefixedMethodsToGetSet = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes combined set/get `value()` to specific `getValue()` or `setValue(x)`.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$object = new InstanceConfigTrait;

$config = $object->config();
$config = $object->config('key');

$object->config('key', 'value');
$object->config(['key' => 'value']);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$object = new InstanceConfigTrait;

$config = $object->getConfig();
$config = $object->getConfig('key');

$object->setConfig('key', 'value');
$object->setConfig(['key' => 'value']);
CODE_SAMPLE
, [self::UNPREFIXED_METHODS_TO_GET_SET => [new ModalToGetSet('InstanceConfigTrait', 'config', 'getConfig', 'setConfig')]])]);
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
        $modalToGetSet = $this->matchTypeAndMethodName($node);
        if (!$modalToGetSet instanceof ModalToGetSet) {
            return null;
        }
        $newName = $this->resolveNewMethodNameByCondition($node, $modalToGetSet);
        $node->name = new Identifier($newName);
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $unprefixedMethodsToGetSet = $configuration[self::UNPREFIXED_METHODS_TO_GET_SET] ?? $configuration;
        Assert::isArray($unprefixedMethodsToGetSet);
        Assert::allIsAOf($unprefixedMethodsToGetSet, ModalToGetSet::class);
        $this->unprefixedMethodsToGetSet = $unprefixedMethodsToGetSet;
    }
    private function matchTypeAndMethodName(MethodCall $methodCall) : ?ModalToGetSet
    {
        foreach ($this->unprefixedMethodsToGetSet as $unprefixedMethodToGetSet) {
            if (!$this->isObjectType($methodCall->var, $unprefixedMethodToGetSet->getObjectType())) {
                continue;
            }
            if (!$this->isName($methodCall->name, $unprefixedMethodToGetSet->getUnprefixedMethod())) {
                continue;
            }
            return $unprefixedMethodToGetSet;
        }
        return null;
    }
    private function resolveNewMethodNameByCondition(MethodCall $methodCall, ModalToGetSet $modalToGetSet) : string
    {
        if (\count($methodCall->args) >= $modalToGetSet->getMinimalSetterArgumentCount()) {
            return $modalToGetSet->getSetMethod();
        }
        if (!isset($methodCall->args[0])) {
            return $modalToGetSet->getGetMethod();
        }
        // first argument type that is considered setter
        if ($modalToGetSet->getFirstArgumentType() === null) {
            return $modalToGetSet->getGetMethod();
        }
        $firstArgumentType = $modalToGetSet->getFirstArgumentType();
        $argumentValue = $methodCall->args[0]->value;
        if ($firstArgumentType === 'array' && $argumentValue instanceof Array_) {
            return $modalToGetSet->getSetMethod();
        }
        return $modalToGetSet->getGetMethod();
    }
}
