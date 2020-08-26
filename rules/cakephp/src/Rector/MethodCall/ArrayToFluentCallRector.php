<?php

declare(strict_types=1);

namespace Rector\CakePHP\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\CakePHP\ValueObject\ArrayItemsAndFluentClass;
use Rector\CakePHP\ValueObject\ArrayToFluentCall;
use Rector\CakePHP\ValueObject\PositionAndClassType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\CakePHP\Tests\Rector\MethodCall\ArrayToFluentCallRector\ArrayToFluentCallRectorTest
 */
final class ArrayToFluentCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ARRAYS_TO_FLUENT_CALLS = 'arrays_to_fluent_calls';

    /**
     * @var string
     */
    public const FACTORY_METHODS = '$factoryMethods';

    /**
     * @var string
     */
    private const ARGUMENT_POSITION = 'argumentPosition';

    /**
     * @var string
     */
    private const CLASSNAME = 'class';

    /**
     * @var ArrayToFluentCall[]
     */
    private $arraysToFluentCalls = [];

    /**
     * @var mixed[]
     */
    private $factoryMethods = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Moves array options to fluent setter method calls.', [
            new ConfiguredCodeSample(
                <<<'PHP'
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
PHP
                ,
                <<<'PHP'
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
PHP
                , [
                    self::ARRAYS_TO_FLUENT_CALLS => [
                        new ArrayToFluentCall('ArticlesTable', [
                            'foreignKey' => 'setForeignKey',
                            'propertyName' => 'setProperty',
                        ]),
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $positionAndClassType = $this->matchTypeAndMethodName($node);
        if ($positionAndClassType === null) {
            return null;
        }

        $fluentMethods = $this->arraysToFluentCalls[$positionAndClassType->getClassType()] ?? [];
        if (! $fluentMethods) {
            return null;
        }

        return $this->replaceArrayToFluentMethodCalls($node, $positionAndClassType->getPosition(), $fluentMethods);
    }

    public function configure(array $configuration): void
    {
        $arraysToFluentCalls = $configuration[self::ARRAYS_TO_FLUENT_CALLS] ?? [];
        Assert::allIsInstanceOf($arraysToFluentCalls, ArrayToFluentCall::class);
        $this->arraysToFluentCalls = $arraysToFluentCalls;

        $this->factoryMethods = $configuration[self::FACTORY_METHODS] ?? [];
    }

    private function matchTypeAndMethodName(MethodCall $methodCall): ?PositionAndClassType
    {
        foreach ($this->factoryMethods as $className => $methodName) {
            if (! $this->isObjectType($methodCall->var, $className)) {
                continue;
            }

            /** @var string[] $methodNames */
            $methodNames = array_keys($methodName);
            if (! $this->isNames($methodCall->name, $methodNames)) {
                continue;
            }

            $currentMethodName = $this->getName($methodCall->name);
            if ($currentMethodName === null) {
                continue;
            }

            $config = $methodName[$currentMethodName];
            $argumentPosition = $config[self::ARGUMENT_POSITION] ?? 1;
            $class = $config[self::CLASSNAME];

            return new PositionAndClassType($argumentPosition, $class);
        }

        return null;
    }

    /**
     * @param string[] $fluentMethods
     */
    private function replaceArrayToFluentMethodCalls(
        MethodCall $methodCall,
        int $argumentPosition,
        array $fluentMethods
    ): ?MethodCall {
        if (count($methodCall->args) !== $argumentPosition) {
            return null;
        }

        $argumentValue = $methodCall->args[$argumentPosition - 1]->value;
        if (! $argumentValue instanceof Array_) {
            return null;
        }

        $arrayItemsAndFluentClass = $this->extractFluentMethods($argumentValue->items, $fluentMethods);

        if ($arrayItemsAndFluentClass->getArrayItems() !== []) {
            $argumentValue->items = $arrayItemsAndFluentClass->getArrayItems();
        } else {
            unset($methodCall->args[$argumentPosition - 1]);
        }

        if ($arrayItemsAndFluentClass->getFluentCalls() === []) {
            return null;
        }

        $node = $methodCall;

        foreach ($arrayItemsAndFluentClass->getFluentCalls() as $method => $arg) {
            $args = $this->createArgs([$arg]);
            $node = $this->createMethodCall($node, $method, $args);
        }

        return $node;
    }

    /**
     * @param array<ArrayItem|null> $originalArrayItems
     * @param string[] $arrayMap
     */
    private function extractFluentMethods(array $originalArrayItems, array $arrayMap): ArrayItemsAndFluentClass
    {
        $newArrayItems = [];
        $fluentCalls = [];

        foreach ($originalArrayItems as $arrayItem) {
            if ($arrayItem === null) {
                continue;
            }

            /** @var ArrayItem $arrayItem */
            $key = $arrayItem->key;

            if ($key instanceof String_ && isset($arrayMap[$key->value])) {
                /** @var string $methodName */
                $methodName = $arrayMap[$key->value];
                $fluentCalls[$methodName] = $arrayItem->value;
            } else {
                $newArrayItems[] = $arrayItem;
            }
        }

        return new ArrayItemsAndFluentClass($newArrayItems, $fluentCalls);
    }
}
