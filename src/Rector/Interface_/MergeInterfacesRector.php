<?php

declare(strict_types=1);

namespace Rector\Core\Rector\Interface_;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * Covers cases like
 * - https://github.com/FriendsOfPHP/PHP-CS-Fixer/commit/a1cdb4d2dd8f45d731244eed406e1d537218cc66
 * - https://github.com/FriendsOfPHP/PHP-CS-Fixer/commit/614d2e6f7af5a5b0be5363ff536aed2b7ee5a31d
 * @see \Rector\Core\Tests\Rector\Interface_\MergeInterfacesRector\MergeInterfacesRectorTest
 */
final class MergeInterfacesRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $oldToNewInterfaces = [];

    /**
     * @param string[] $oldToNewInterfaces
     */
    public function __construct(array $oldToNewInterfaces = [])
    {
        $this->oldToNewInterfaces = $oldToNewInterfaces;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Merges old interface to a new one, that already has its methods', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass implements SomeInterface, SomeOldInterface
{
}
PHP
                ,
                <<<'PHP'
class SomeClass implements SomeInterface
{
}
PHP
                ,
                [
                    'SomeOldInterface' => 'SomeInterface',
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
            if (! $this->isNames($implement, array_keys($this->oldToNewInterfaces))) {
                continue;
            }

            $interface = $this->getName($implement);
            $node->implements[$key] = new Name($this->oldToNewInterfaces[$interface]);
        }

        $this->makeImplementsUnique($node);

        return $node;
    }

    private function makeImplementsUnique(Class_ $classNode): void
    {
        $alreadyAddedNames = [];
        foreach ($classNode->implements as $key => $name) {
            $fqnName = $this->getName($name);
            if (in_array($fqnName, $alreadyAddedNames, true)) {
                $this->removeImplements($classNode, $key);
                continue;
            }

            $alreadyAddedNames[] = $fqnName;
        }
    }
}
