<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202411\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\String_\RenameStringRector\RenameStringRectorTest
 */
final class RenameStringRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var array<string, string>
     */
    private $stringChanges = [];
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
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
