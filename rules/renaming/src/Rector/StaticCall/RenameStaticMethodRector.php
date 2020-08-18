<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Renaming\Tests\Rector\StaticCall\RenameStaticMethodRector\RenameStaticMethodRectorTest
 */
final class RenameStaticMethodRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const OLD_TO_NEW_METHODS_BY_CLASSES = '$oldToNewMethodByClasses';

    /**
     * @var string[][]|string[][][]
     */
    private $oldToNewMethodByClasses = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns method names to new ones.', [
            new ConfiguredCodeSample(
                'SomeClass::oldStaticMethod();',
                'AnotherExampleClass::newStaticMethod();',
                [
                    self::OLD_TO_NEW_METHODS_BY_CLASSES => [
                        'SomeClass' => [
                            'oldMethod' => ['AnotherExampleClass', 'newStaticMethod'],
                        ],
                    ],
                ]
            ),
            new ConfiguredCodeSample(
                'SomeClass::oldStaticMethod();',
                'SomeClass::newStaticMethod();',
                [
                    self::OLD_TO_NEW_METHODS_BY_CLASSES => [
                        'SomeClass' => [
                            'oldMethod' => 'newStaticMethod',
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
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->oldToNewMethodByClasses as $type => $oldToNewMethods) {
            if (! $this->isObjectType($node->class, $type)) {
                continue;
            }

            foreach ($oldToNewMethods as $oldMethod => $newMethod) {
                if (! $this->isName($node->name, $oldMethod)) {
                    continue;
                }

                return $this->rename($node, $newMethod);
            }
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->oldToNewMethodByClasses = $configuration[self::OLD_TO_NEW_METHODS_BY_CLASSES] ?? [];
    }

    /**
     * @param string|string[] $newMethod
     */
    private function rename(StaticCall $staticCall, $newMethod): StaticCall
    {
        if (is_array($newMethod)) {
            [$newClass, $newMethod] = $newMethod;
            $staticCall->class = new Name($newClass);
            $staticCall->name = new Identifier($newMethod);
        } else {
            $staticCall->name = new Identifier($newMethod);
        }

        return $staticCall;
    }
}
