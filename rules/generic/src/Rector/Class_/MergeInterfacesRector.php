<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Covers cases like
 * - https://github.com/FriendsOfPHP/PHP-CS-Fixer/commit/a1cdb4d2dd8f45d731244eed406e1d537218cc66
 * - https://github.com/FriendsOfPHP/PHP-CS-Fixer/commit/614d2e6f7af5a5b0be5363ff536aed2b7ee5a31d
 *
 * @see \Rector\Generic\Tests\Rector\Class_\MergeInterfacesRector\MergeInterfacesRectorTest
 */
final class MergeInterfacesRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const OLD_TO_NEW_INTERFACES = 'old_to_new_interfaces';

    /**
     * @var string[]
     */
    private $oldToNewInterfaces = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Merges old interface to a new one, that already has its methods', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass implements SomeInterface, SomeOldInterface
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass implements SomeInterface
{
}
CODE_SAMPLE
                ,
                [
                    self::OLD_TO_NEW_INTERFACES => [
                        'SomeOldInterface' => 'SomeInterface',
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->implements === []) {
            return null;
        }

        foreach ($node->implements as $key => $implement) {
            $oldInterfaces = array_keys($this->oldToNewInterfaces);
            if (! $this->isNames($implement, $oldInterfaces)) {
                continue;
            }

            $interface = $this->getName($implement);
            $node->implements[$key] = new Name($this->oldToNewInterfaces[$interface]);
        }

        $this->makeImplementsUnique($node);

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->oldToNewInterfaces = $configuration[self::OLD_TO_NEW_INTERFACES] ?? [];
    }

    private function makeImplementsUnique(Class_ $class): void
    {
        $alreadyAddedNames = [];
        foreach ($class->implements as $key => $name) {
            $fqnName = $this->getName($name);
            if (in_array($fqnName, $alreadyAddedNames, true)) {
                $this->nodeRemover->removeImplements($class, $key);
                continue;
            }

            $alreadyAddedNames[] = $fqnName;
        }
    }
}
