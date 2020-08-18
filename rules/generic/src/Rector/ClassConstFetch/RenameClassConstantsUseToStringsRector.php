<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Generic\Tests\Rector\ClassConstFetch\RenameClassConstantsUseToStringsRector\RenameClassConstantsUseToStringsRectorTest
 */
final class RenameClassConstantsUseToStringsRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const OLD_CONSTANTS_TO_NEW_VALUES_BY_TYPE = '$oldConstantsToNewValuesByType';

    /**
     * @var string[][]
     */
    private $oldConstantsToNewValuesByType = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces constant by value', [
            new ConfiguredCodeSample(
                '$value === Nette\Configurator::DEVELOPMENT',
                '$value === "development"',
                [
                    self::OLD_CONSTANTS_TO_NEW_VALUES_BY_TYPE => [
                        'Nette\Configurator' => [
                            'DEVELOPMENT' => 'development',
                            'PRODUCTION' => 'production',
                        ],
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
        foreach ($this->oldConstantsToNewValuesByType as $type => $oldConstantsToNewValues) {
            if (! $this->isObjectType($node->class, $type)) {
                continue;
            }

            foreach ($oldConstantsToNewValues as $oldConstant => $newValue) {
                if (! $this->isName($node->name, $oldConstant)) {
                    continue;
                }

                return new String_($newValue);
            }
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->oldConstantsToNewValuesByType = $configuration[self::OLD_CONSTANTS_TO_NEW_VALUES_BY_TYPE] ?? [];
    }
}
