<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\ClassConstFetch;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstantRector\RenameClassConstantRectorTest
 */
final class RenameClassConstantRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const OLD_TO_NEW_CONSTANTS_BY_CLASS = '$oldToNewConstantsByClass';

    /**
     * class => [
     *      OLD_CONSTANT => NEW_CONSTANT
     * ]
     *
     * @var array<string, array<string, string>>
     */
    private $oldToNewConstantsByClass = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces defined class constants in their calls.', [
            new ConfiguredCodeSample(
                <<<'PHP'
$value = SomeClass::OLD_CONSTANT;
$value = SomeClass::OTHER_OLD_CONSTANT;
PHP
                ,
                <<<'PHP'
$value = SomeClass::NEW_CONSTANT;
$value = DifferentClass::NEW_CONSTANT;
PHP
                ,
                [
                    self::OLD_TO_NEW_CONSTANTS_BY_CLASS => [
                        'SomeClass' => [
                            'OLD_CONSTANT' => 'NEW_CONSTANT',
                            'OTHER_OLD_CONSTANT' => 'DifferentClass::NEW_CONSTANT',
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
        foreach ($this->oldToNewConstantsByClass as $type => $oldToNewConstants) {
            if (! $this->isObjectType($node, $type)) {
                continue;
            }

            foreach ($oldToNewConstants as $oldConstant => $newConstant) {
                if (! $this->isName($node->name, $oldConstant)) {
                    continue;
                }

                if (Strings::contains($newConstant, '::')) {
                    return $this->createClassConstantFetchNodeFromDoubleColonFormat($newConstant);
                }

                $node->name = new Identifier($newConstant);

                return $node;
            }
        }

        return $node;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $this->oldToNewConstantsByClass = $configuration[self::OLD_TO_NEW_CONSTANTS_BY_CLASS] ?? [];
    }

    private function createClassConstantFetchNodeFromDoubleColonFormat(string $constant): ClassConstFetch
    {
        [$constantClass, $constantName] = explode('::', $constant);

        return new ClassConstFetch(new FullyQualified($constantClass), new Identifier($constantName));
    }
}
