<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220501\Webmozart\Assert\Assert;
/**
 * Covers cases like
 * - https://github.com/FriendsOfPHP/PHP-CS-Fixer/commit/a1cdb4d2dd8f45d731244eed406e1d537218cc66
 * - https://github.com/FriendsOfPHP/PHP-CS-Fixer/commit/614d2e6f7af5a5b0be5363ff536aed2b7ee5a31d
 *
 * @see \Rector\Tests\Transform\Rector\Class_\MergeInterfacesRector\MergeInterfacesRectorTest
 */
final class MergeInterfacesRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var array<string, string>
     */
    private $oldToNewInterfaces = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Merges old interface to a new one, that already has its methods', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
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
            $node->implements[$key] = new \PhpParser\Node\Name($this->oldToNewInterfaces[$interface]);
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
        \RectorPrefix20220501\Webmozart\Assert\Assert::allString(\array_keys($configuration));
        \RectorPrefix20220501\Webmozart\Assert\Assert::allString($configuration);
        $this->oldToNewInterfaces = $configuration;
    }
    private function makeImplementsUnique(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        $alreadyAddedNames = [];
        foreach ($class->implements as $key => $name) {
            $fqnName = $this->getName($name);
            if (\in_array($fqnName, $alreadyAddedNames, \true)) {
                $this->nodeRemover->removeImplements($class, $key);
                continue;
            }
            $alreadyAddedNames[] = $fqnName;
        }
    }
}
