<?php

declare(strict_types=1);

namespace Rector\CakePHP\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CakePHP\Tests\Rector\MethodCall\ArrayToFluentCallRector\ArrayToFluentCallRectorTest
 */
final class ArrayToFluentCallRector extends AbstractRector
{
    /**
     * @var string
     */
    private const ARGUMENT_POSITION = 'argumentPosition';

    /**
     * @var string
     */
    private const CLASSNAME = 'class';

    /**
     * @var mixed[]
     */
    private $configurableClasses = [];

    /**
     * @var mixed[]
     */
    private $factoryMethods = [];

    /**
     * @param mixed[] $factoryMethods
     */
    public function __construct(array $configurableClasses = [], array $factoryMethods = [])
    {
        $this->configurableClasses = $configurableClasses;
        $this->factoryMethods = $factoryMethods;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Moves array options to fluent setter method calls.', [
            new CodeSample(
                <<<'PHP'
class ArticlesTable extends \Cake\ORM\Table
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
class ArticlesTable extends \Cake\ORM\Table
{
    public function initialize(array $config)
    {
        $this->belongsTo('Authors')
            ->setForeignKey('author_id')
            ->setProperty('person');
    }
}
PHP
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
        $config = $this->matchTypeAndMethodName($node);
        if ($config === null) {
            return null;
        }

        [$argumentPosition, $class] = $config;

        $fluentMethods = $this->configurableClasses[$class] ?? [];
        if (! $fluentMethods) {
            return null;
        }

        return $this->replaceArrayToFluentMethodCalls($node, $argumentPosition, $fluentMethods);
    }

    /**
     * @return mixed[]|null
     */
    private function matchTypeAndMethodName(MethodCall $methodCall): ?array
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

            return [$argumentPosition, $class];
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

        [$arrayItems, $fluentCalls] = $this->extractFluentMethods($argumentValue->items, $fluentMethods);

        if ($arrayItems) {
            $argumentValue->items = $arrayItems;
        } else {
            unset($methodCall->args[$argumentPosition - 1]);
        }

        if (! $fluentCalls) {
            return null;
        }

        $node = $methodCall;

        foreach ($fluentCalls as $method => $arg) {
            $args = $this->createArgs([$arg]);
            $node = $this->createMethodCall($node, $method, $args);
        }

        return $node;
    }

    /**
     * @param ArrayItem[] $originalArrayItems
     * @param string[] $arrayMap
     */
    private function extractFluentMethods(array $originalArrayItems, array $arrayMap): array
    {
        $newArrayItems = [];
        $fluentCalls = [];

        foreach ($originalArrayItems as $arrayItem) {
            $key = $arrayItem->key;

            if ($key instanceof String_ && isset($arrayMap[$key->value])) {
                $fluentCalls[$arrayMap[$key->value]] = $arrayItem->value;
            } else {
                $newArrayItems[] = $arrayItem;
            }
        }

        return [$newArrayItems, $fluentCalls];
    }
}
