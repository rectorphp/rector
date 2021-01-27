<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\ClassConstFetch;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Generic\ValueObject\ClassConstFetchToValue;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Generic\Tests\Rector\ClassConstFetch\RenameClassConstantsUseToStringsRector\RenameClassConstantsUseToStringsRectorTest
 */
final class RenameClassConstantsUseToStringsRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const CLASS_CONST_FETCHES_TO_VALUES = 'old_constants_to_new_valuesByType';

    /**
     * @var ClassConstFetchToValue[]
     */
    private $classConstFetchesToValues = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces constant by value', [
            new ConfiguredCodeSample(
                '$value === Nette\Configurator::DEVELOPMENT',
                '$value === "development"',
                [
                    self::CLASS_CONST_FETCHES_TO_VALUES => [
                        new ClassConstFetchToValue('Nette\Configurator', 'DEVELOPMENT', 'development'),
                        new ClassConstFetchToValue('Nette\Configurator', 'PRODUCTION', 'production'),
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
        return [ClassConstFetch::class];
    }

    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->classConstFetchesToValues as $classConstFetchToValue) {
            if (! $this->isObjectType($node->class, $classConstFetchToValue->getClass())) {
                continue;
            }

            if (! $this->isName($node->name, $classConstFetchToValue->getConstant())) {
                continue;
            }

            return BuilderHelpers::normalizeValue($classConstFetchToValue->getValue());
        }

        return $node;
    }

    /**
     * @param array<string, ClassConstFetchToValue[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $classConstFetchesToValues = $configuration[self::CLASS_CONST_FETCHES_TO_VALUES] ?? [];
        Assert::allIsInstanceOf($classConstFetchesToValues, ClassConstFetchToValue::class);

        $this->classConstFetchesToValues = $classConstFetchesToValues;
    }
}
