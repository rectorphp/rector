<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202211\Webmozart\Assert\Assert;
/**
 * @changelog https://github.com/symfony/symfony/pull/35858
 *
 * @see \Rector\Tests\Renaming\Rector\String_\RenameStringRector\RenameStringRectorTest
 */
final class RenameStringRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<string, string>
     */
    private $stringChanges = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change string value', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return 'ROLE_PREVIOUS_ADMIN';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return 'IS_IMPERSONATOR';
    }
}
CODE_SAMPLE
, ['ROLE_PREVIOUS_ADMIN' => 'IS_IMPERSONATOR'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [String_::class];
    }
    /**
     * @param String_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        foreach ($this->stringChanges as $oldValue => $newValue) {
            if (!$this->valueResolver->isValue($node, $oldValue)) {
                continue;
            }
            return new String_($newValue);
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allString(\array_keys($configuration));
        Assert::allString($configuration);
        $this->stringChanges = $configuration;
    }
}
