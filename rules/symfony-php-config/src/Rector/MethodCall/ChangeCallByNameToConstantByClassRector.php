<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\PhpParser\Node\Manipulator\ChainMethodCallManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\SymfonyPhpConfig\NodeAnalyzer\SymfonyPhpConfigClosureAnalyzer;

/**
 * @see \Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ChangeCallByNameToConstantByClassRector\ChangeCallByNameToConstantByClassRectorTest
 */
final class ChangeCallByNameToConstantByClassRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const CLASS_TYPES_TO_METHOD_NAME = 'class_types_to_method_name';

    /**
     * @var mixed[]
     */
    private $classTypesToMethodName = [];

    /**
     * @var SymfonyPhpConfigClosureAnalyzer
     */
    private $symfonyPhpConfigClosureAnalyzer;

    /**
     * @var ChainMethodCallManipulator
     */
    private $chainMethodCallManipulator;

    public function __construct(
        SymfonyPhpConfigClosureAnalyzer $symfonyPhpConfigClosureAnalyzer,
        ChainMethodCallManipulator $chainMethodCallManipulator
    ) {
        $this->symfonyPhpConfigClosureAnalyzer = $symfonyPhpConfigClosureAnalyzer;
        $this->chainMethodCallManipulator = $chainMethodCallManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change $service->call(...) to constant', [
            new ConfiguredCodeSample(
                <<<'PHP'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeClass::class)
        ->call('configure', [[
            '$key' => 'value'
        ]]);
}
PHP
,
                <<<'PHP'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeClass::class)
        ->call('configure', [[
            SomeClass::KEY => 'value'
        ]]);
}
PHP
,
                [
                    self::CLASS_TYPES_TO_METHOD_NAME => [
                        'SomeClass' => 'configure',
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        $rootSetMethodCall = $this->matchRootSetMethodCall($node);
        if ($rootSetMethodCall === null) {
            return null;
        }

        foreach ($this->classTypesToMethodName as $classType => $methodName) {
            $idValue = $this->getValue($rootSetMethodCall->args[0]->value);
            $nestedArray = $this->matchTypeMethodNameAndNestedArray($idValue, $classType, $node, $methodName);
            if ($nestedArray === null) {
                continue;
            }

            foreach ($nestedArray->items as $nestedArrayItem) {
                // replace key with class const fetch
                if (! $nestedArrayItem->key instanceof String_) {
                    continue;
                }

                $keyValue = $nestedArrayItem->key->value;
                $keyValue = ltrim($keyValue, '$');
                $keyConstant = StaticRectorStrings::camelToConstant($keyValue);

                $nestedArrayItem->key = new ClassConstFetch(new FullyQualified($idValue), $keyConstant);
            }
        }

        return $node;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $this->classTypesToMethodName = $configuration[self::CLASS_TYPES_TO_METHOD_NAME] ?? [];
    }

    private function shouldSkip(MethodCall $methodCall): bool
    {
        $closure = $methodCall->getAttribute(AttributeKey::CLOSURE_NODE);
        if ($closure === null) {
            return true;
        }

        if (! $this->symfonyPhpConfigClosureAnalyzer->isPhpConfigClosure($closure)) {
            return true;
        }

        if (! $this->isName($methodCall->name, 'call')) {
            return true;
        }

        return false;
    }

    private function matchRootSetMethodCall(MethodCall $methodCall): ?MethodCall
    {
        $rootMethodCall = $this->chainMethodCallManipulator->resolveRootMethodCall($methodCall);
        if ($rootMethodCall === null) {
            return null;
        }

        // $services->set(...)
        if (! $this->isName($rootMethodCall->name, 'set')) {
            return null;
        }

        return $rootMethodCall;
    }

    /**
     * @param mixed $idValue
     */
    private function matchTypeMethodNameAndNestedArray(
        $idValue,
        string $classType,
        MethodCall $methodCall,
        string $methodName
    ): ?Array_ {
        if (! is_string($idValue)) {
            return null;
        }

        if (! is_a($idValue, $classType, true)) {
            return null;
        }

        if (! $this->isValue($methodCall->args[0]->value, $methodName)) {
            return null;
        }

        $array = $methodCall->args[1]->value;
        if (! $array instanceof Array_) {
            return null;
        }

        if (count($array->items) > 1) {
            return null;
        }

        $nestedArray = $array->items[0]->value;
        if (! $nestedArray instanceof Array_) {
            return null;
        }

        return $nestedArray;
    }
}
