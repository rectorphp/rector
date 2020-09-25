<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Renaming\ValueObject\RenameStaticMethod;

/**
 * @see \Rector\Renaming\Tests\Rector\StaticCall\RenameStaticMethodRector\RenameStaticMethodRectorTest
 */
final class RenameStaticMethodRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const OLD_TO_NEW_METHODS_BY_CLASSES = 'old_to_new_method_by_classes';

    /**
     * @var string
     */
    private const SOME_CLASS = 'SomeClass';

    /**
     * @var RenameStaticMethod[]
     */
    private $staticMethodRenames = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns method names to new ones.', [
            new ConfiguredCodeSample(
                'SomeClass::oldStaticMethod();',
                'AnotherExampleClass::newStaticMethod();',
                [
                    self::OLD_TO_NEW_METHODS_BY_CLASSES => [
                        new RenameStaticMethod(self::SOME_CLASS, 'oldMethod', 'AnotherExampleClass', 'newStaticMethod'),
                    ],
                ]
            ),
            new ConfiguredCodeSample(
                'SomeClass::oldStaticMethod();',
                'SomeClass::newStaticMethod();',
                [
                    self::OLD_TO_NEW_METHODS_BY_CLASSES => [
                        new RenameStaticMethod(self::SOME_CLASS, 'oldMethod', self::SOME_CLASS, 'newStaticMethod'),
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
        foreach ($this->staticMethodRenames as $staticMethodRename) {
            if (! $this->isObjectType($node->class, $staticMethodRename->getOldClass())) {
                continue;
            }

            if (! $this->isName($node->name, $staticMethodRename->getOldMethod())) {
                continue;
            }

            return $this->rename($node, $staticMethodRename);
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->staticMethodRenames = $configuration[self::OLD_TO_NEW_METHODS_BY_CLASSES] ?? [];
    }

    private function rename(StaticCall $staticCall, RenameStaticMethod $renameStaticMethod): StaticCall
    {
        $staticCall->name = new Identifier($renameStaticMethod->getNewMethod());

        if ($renameStaticMethod->hasClassChanged()) {
            $staticCall->class = new FullyQualified($renameStaticMethod->getNewClass());
        }

        return $staticCall;
    }
}
