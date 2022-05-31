<?php

declare (strict_types=1);
namespace Rector\CakePHP\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\CakePHP\ValueObject\ArrayItemsAndFluentClass;
use Rector\CakePHP\ValueObject\ArrayToFluentCall;
use Rector\CakePHP\ValueObject\FactoryMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220531\Webmozart\Assert\Assert;
/**
 * @see \Rector\CakePHP\Tests\Rector\MethodCall\ArrayToFluentCallRector\ArrayToFluentCallRectorTest
 */
final class ArrayToFluentCallRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ARRAYS_TO_FLUENT_CALLS = 'arrays_to_fluent_calls';
    /**
     * @var string
     */
    public const FACTORY_METHODS = 'factory_methods';
    /**
     * @var ArrayToFluentCall[]
     */
    private $arraysToFluentCalls = [];
    /**
     * @var FactoryMethod[]
     */
    private $factoryMethods = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Moves array options to fluent setter method calls.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
use Cake\ORM\Table;

final class ArticlesTable extends Table
{
    public function initialize(array $config)
    {
        $this->belongsTo('Authors', [
            'foreignKey' => 'author_id',
            'propertyName' => 'person'
        ]);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Cake\ORM\Table;

final class ArticlesTable extends Table
{
    public function initialize(array $config)
    {
        $this->belongsTo('Authors')
            ->setForeignKey('author_id')
            ->setProperty('person');
    }
}
CODE_SAMPLE
, [self::ARRAYS_TO_FLUENT_CALLS => [new \Rector\CakePHP\ValueObject\ArrayToFluentCall('ArticlesTable', ['foreignKey' => 'setForeignKey', 'propertyName' => 'setProperty'])]])]);
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
        $factoryMethod = $this->matchTypeAndMethodName($node);
        if (!$factoryMethod instanceof \Rector\CakePHP\ValueObject\FactoryMethod) {
            return null;
        }
        foreach ($this->arraysToFluentCalls as $arrayToFluentCall) {
            if ($arrayToFluentCall->getClass() !== $factoryMethod->getNewClass()) {
                continue;
            }
            return $this->replaceArrayToFluentMethodCalls($node, $factoryMethod->getPosition(), $arrayToFluentCall);
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $arraysToFluentCalls = $configuration[self::ARRAYS_TO_FLUENT_CALLS] ?? [];
        \RectorPrefix20220531\Webmozart\Assert\Assert::isArray($arraysToFluentCalls);
        \RectorPrefix20220531\Webmozart\Assert\Assert::allIsInstanceOf($arraysToFluentCalls, \Rector\CakePHP\ValueObject\ArrayToFluentCall::class);
        $this->arraysToFluentCalls = $arraysToFluentCalls;
        $factoryMethods = $configuration[self::FACTORY_METHODS] ?? [];
        \RectorPrefix20220531\Webmozart\Assert\Assert::isArray($factoryMethods);
        \RectorPrefix20220531\Webmozart\Assert\Assert::allIsInstanceOf($factoryMethods, \Rector\CakePHP\ValueObject\FactoryMethod::class);
        $this->factoryMethods = $factoryMethods;
    }
    private function matchTypeAndMethodName(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\Rector\CakePHP\ValueObject\FactoryMethod
    {
        foreach ($this->factoryMethods as $factoryMethod) {
            if (!$this->isObjectType($methodCall->var, $factoryMethod->getObjectType())) {
                continue;
            }
            if (!$this->isName($methodCall->name, $factoryMethod->getMethod())) {
                continue;
            }
            return $factoryMethod;
        }
        return null;
    }
    private function replaceArrayToFluentMethodCalls(\PhpParser\Node\Expr\MethodCall $methodCall, int $argumentPosition, \Rector\CakePHP\ValueObject\ArrayToFluentCall $arrayToFluentCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        if (\count($methodCall->args) !== $argumentPosition) {
            return null;
        }
        $argumentValue = $methodCall->args[$argumentPosition - 1]->value;
        if (!$argumentValue instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $arrayItemsAndFluentClass = $this->extractFluentMethods($argumentValue->items, $arrayToFluentCall->getArrayKeysToFluentCalls());
        if ($arrayItemsAndFluentClass->getArrayItems() !== []) {
            $argumentValue->items = $arrayItemsAndFluentClass->getArrayItems();
        } else {
            $positionToRemove = $argumentPosition - 1;
            $this->nodeRemover->removeArg($methodCall, $positionToRemove);
        }
        if ($arrayItemsAndFluentClass->getFluentCalls() === []) {
            return null;
        }
        $node = $methodCall;
        foreach ($arrayItemsAndFluentClass->getFluentCalls() as $method => $expr) {
            $args = $this->nodeFactory->createArgs([$expr]);
            $node = $this->nodeFactory->createMethodCall($node, $method, $args);
        }
        return $node;
    }
    /**
     * @param array<ArrayItem|null> $originalArrayItems
     * @param array<string, string> $arrayMap
     */
    private function extractFluentMethods(array $originalArrayItems, array $arrayMap) : \Rector\CakePHP\ValueObject\ArrayItemsAndFluentClass
    {
        $newArrayItems = [];
        $fluentCalls = [];
        foreach ($originalArrayItems as $originalArrayItem) {
            if ($originalArrayItem === null) {
                continue;
            }
            $key = $originalArrayItem->key;
            if ($key instanceof \PhpParser\Node\Scalar\String_ && isset($arrayMap[$key->value])) {
                /** @var string $methodName */
                $methodName = $arrayMap[$key->value];
                $fluentCalls[$methodName] = $originalArrayItem->value;
            } else {
                $newArrayItems[] = $originalArrayItem;
            }
        }
        return new \Rector\CakePHP\ValueObject\ArrayItemsAndFluentClass($newArrayItems, $fluentCalls);
    }
}
