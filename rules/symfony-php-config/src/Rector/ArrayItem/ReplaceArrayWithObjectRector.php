<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Rector\ArrayItem;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Renaming\ValueObject\MethodCallRename;

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
            if (! $this->isValue($node->key, $constantName)) {
                continue;
            }

            // already converted
            if ($node->value instanceof FuncCall) {
                return null;
            }

            if (! $node->value instanceof Array_) {
                return null;
            }

            /** @var Array_ $configurationValueArray */
            $configurationValueArray = $node->value;

            $newItems = [];
            foreach ($node->value->items as $key => $nestedArrayItem) {
                if (! $nestedArrayItem instanceof ArrayItem) {
                    continue;
                }

                if (! $nestedArrayItem->key instanceof String_) {
                    throw new ShouldNotHappenException();
                }

                if (! $nestedArrayItem->value instanceof Array_) {
                    continue;
                }

                foreach ($nestedArrayItem->value->items as $nestedNestedArrayItem) {
                    $ars = [$nestedArrayItem->key, $nestedNestedArrayItem->key, $nestedNestedArrayItem->value];
                    $newItems[] = new New_(new FullyQualified($valueObjectClass), $ars);
                }
            }

            $configurationValueArray->items = $newItems;

            // wrap with inline objects
            $args = [new Arg($configurationValueArray)];
            $node->value = new FuncCall(new FullyQualified('Rector\SymfonyPhpConfig\inline_objects'), $args);

            return $node;
        }

        return null;
    }

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
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                'Illuminate\Auth\Access\Gate' => [
                    'access' => 'inspect',
                ]
            ]]
        ]);
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

    public function configure(array $configuration): void
    {
        $this->constantNamesToValueObjects = $configuration[self::CONSTANT_NAMES_TO_VALUE_OBJECTS] ?? [];
    }
}
