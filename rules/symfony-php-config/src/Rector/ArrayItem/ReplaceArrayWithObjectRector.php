<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Rector\ArrayItem;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Renaming\ValueObject\MethodCallRename;

/**
 * @see \Rector\SymfonyPhpConfig\Tests\Rector\ArrayItem\ReplaceArrayWithObjectRector\ReplaceArrayWithObjectRectorTest
 */
final class ReplaceArrayWithObjectRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const CONSTANT_NAMES_TO_VALUE_OBJECTS = 'constant_names_to_value_objects';

    /**
     * @var array<string, string>
     */
    private $constantNamesToValueObjects = [];

    /**
     * @var mixed[]
     */
    private $newItems = [];

    /**
     * @var mixed[]
     */
    private $arguments = [];

    /**
     * @var Expr[]
     */
    private $nestedArguments = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace complex array configuration in configs with value object', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                'Illuminate\Auth\Access\Gate' => [
                    'access' => 'inspect',
                ]
            ]]
        ]);
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => \Rector\SymfonyPhpConfig\inline_value_objects([
                new \Rector\Renaming\ValueObject\MethodCallRename('Illuminate\Auth\Access\Gate', 'access', 'inspect'),
            ])
        ]]);
}
CODE_SAMPLE
                ,
                [
                    self::CONSTANT_NAMES_TO_VALUE_OBJECTS => [
                        'Rector\Renaming\Rector\MethodCall\RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS' => MethodCallRename::class,
                    ],
                ]),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ArrayItem::class];
    }

    /**
     * @param ArrayItem $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->key instanceof ClassConstFetch) {
            return null;
        }

        foreach ($this->constantNamesToValueObjects as $constantName => $valueObjectClass) {
            if ($this->shouldSkip($node, $constantName)) {
                continue;
            }

            /** @var Array_ $configurationValueArray */
            $configurationValueArray = $node->value;

            $this->newItems = [];
            foreach ($configurationValueArray->items as $nestedArrayItem) {
                if ($this->shouldSkipNestedArrayItem($nestedArrayItem)) {
                    continue;
                }

                /** @var ArrayItem $nestedArrayItem */
                $this->arguments = [$nestedArrayItem->key];

                $this->nestedArguments = [];

                $this->collectArgumentsFromNestedArrayAndCreateNew($nestedArrayItem->value, $valueObjectClass);
            }

            $configurationValueArray->items = $this->newItems;

            // wrap with inline objects
            $args = [new Arg($configurationValueArray)];
            $node->value = new FuncCall(new FullyQualified('Rector\SymfonyPhpConfig\inline_value_objects'), $args);

            return $node;
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->constantNamesToValueObjects = $configuration[self::CONSTANT_NAMES_TO_VALUE_OBJECTS] ?? [];
    }

    private function shouldSkip(ArrayItem $arrayItem, string $constantName): bool
    {
        if ($arrayItem->key === null) {
            return true;
        }

        if (! $this->isValue($arrayItem->key, $constantName)) {
            return true;
        }

        // already converted
        if ($arrayItem->value instanceof FuncCall) {
            return true;
        }

        return ! $arrayItem->value instanceof Array_;
    }

    private function shouldSkipNestedArrayItem(?ArrayItem $nestedArrayItem): bool
    {
        if (! $nestedArrayItem instanceof ArrayItem) {
            return true;
        }

        if ($nestedArrayItem->key === null) {
            return true;
        }

        if (! $nestedArrayItem->key instanceof String_) {
            return true;
        }

        return ! $nestedArrayItem->value instanceof Array_;
    }

    private function collectArgumentsFromNestedArrayAndCreateNew(Node $valueNode, string $valueObjectClass): void
    {
        if ($valueNode instanceof Array_) {
            foreach ((array) $valueNode->items as $key => $arrayItem) {
                if (! $arrayItem instanceof ArrayItem) {
                    continue;
                }

                $this->collectNestedArguments($arrayItem, $key);

                $this->collectArgumentsFromNestedArrayAndCreateNew($arrayItem->value, $valueObjectClass);
            }
        } else {
            $arguments = array_merge($this->arguments, $this->nestedArguments);

            $args = $this->createArgs($arguments);

            $new = new New_(new FullyQualified($valueObjectClass), $args);
            $this->newItems[] = new ArrayItem($new);

            $this->nestedArguments = [];
        }
    }

    private function collectNestedArguments(ArrayItem $arrayItem, int $key): void
    {
        $this->nestedArguments[] = $arrayItem->key === null ? new LNumber($key) : $arrayItem->key;

        if (! $arrayItem->value instanceof Array_) {
            $this->nestedArguments[] = $arrayItem->value;
        }
    }
}
