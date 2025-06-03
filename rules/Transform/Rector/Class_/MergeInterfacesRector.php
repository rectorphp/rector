<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202506\Webmozart\Assert\Assert;
/**
 * Covers cases like
 * - https://github.com/FriendsOfPHP/PHP-CS-Fixer/commit/a1cdb4d2dd8f45d731244eed406e1d537218cc66
 * - https://github.com/FriendsOfPHP/PHP-CS-Fixer/commit/614d2e6f7af5a5b0be5363ff536aed2b7ee5a31d
 *
 * @see \Rector\Tests\Transform\Rector\Class_\MergeInterfacesRector\MergeInterfacesRectorTest
 */
final class MergeInterfacesRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<string, string>
     */
    private array $oldToNewInterfaces = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Merge old interface to a new one, that already has its methods', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass implements SomeInterface, SomeOldInterface
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass implements SomeInterface
{
}
CODE_SAMPLE
, ['SomeOldInterface' => 'SomeInterface'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->implements === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->implements as $key => $implement) {
            $oldInterfaces = \array_keys($this->oldToNewInterfaces);
            if (!$this->isNames($implement, $oldInterfaces)) {
                continue;
            }
            $interface = $this->getName($implement);
            $node->implements[$key] = new FullyQualified($this->oldToNewInterfaces[$interface]);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        $this->makeImplementsUnique($node);
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allString(\array_keys($configuration));
        Assert::allString($configuration);
        $this->oldToNewInterfaces = $configuration;
    }
    private function makeImplementsUnique(Class_ $class) : void
    {
        $alreadyAddedNames = [];
        /** @var array<int, Interface_> $implements */
        $implements = $class->implements;
        foreach ($implements as $key => $name) {
            $fqnName = $this->getName($name);
            if (\in_array($fqnName, $alreadyAddedNames, \true)) {
                unset($class->implements[$key]);
                continue;
            }
            $alreadyAddedNames[] = $fqnName;
        }
    }
}
