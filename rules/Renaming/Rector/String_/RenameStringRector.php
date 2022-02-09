<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220209\Webmozart\Assert\Assert;
/**
 * @changelog https://github.com/symfony/symfony/pull/35858
 *
 * @see \Rector\Tests\Renaming\Rector\String_\RenameStringRector\RenameStringRectorTest
 */
final class RenameStringRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @deprecated
     * @var string
     */
    public const STRING_CHANGES = 'string_changes';
    /**
     * @var array<string, string>
     */
    private $stringChanges = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change string value', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Scalar\String_::class];
    }
    /**
     * @param String_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach ($this->stringChanges as $oldValue => $newValue) {
            if (!$this->valueResolver->isValue($node, $oldValue)) {
                continue;
            }
            return new \PhpParser\Node\Scalar\String_($newValue);
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $stringChanges = $configuration[self::STRING_CHANGES] ?? $configuration;
        \RectorPrefix20220209\Webmozart\Assert\Assert::isArray($stringChanges);
        \RectorPrefix20220209\Webmozart\Assert\Assert::allString(\array_keys($stringChanges));
        \RectorPrefix20220209\Webmozart\Assert\Assert::allString($stringChanges);
        $this->stringChanges = $stringChanges;
    }
}
