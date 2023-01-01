<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use RectorPrefix202301\Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Moving doc types to type declarations is dangerous. Use specific strict types instead.
 * This rule will be split info many small ones.
 */
final class TypedPropertyRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @api
     * @var string
     */
    public const INLINE_PUBLIC = 'inline_public';
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes property type by `@var` annotations or default value.', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @var int
     */
    private $count;

    private $isDone = false;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private int $count;

    private bool $isDone = false;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        $this->symfonyStyle->error('The TypedPropertyRector rule is deprecated, as it works with doc block types that are not reliable and adds invalid  types');
        \sleep(5);
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
}
