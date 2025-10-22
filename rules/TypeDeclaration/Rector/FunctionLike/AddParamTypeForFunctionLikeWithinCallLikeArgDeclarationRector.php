<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\StringType;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeForFunctionLikeWithinCallLikeArgDeclaration;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202510\Webmozart\Assert\Assert;
/**
 * @deprecated as too specific and not useful in Rector core. Implement it locally instead if needed.
 */
final class AddParamTypeForFunctionLikeWithinCallLikeArgDeclarationRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add param types where needed', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
(new SomeClass)->process(function ($parameter) {});
CODE_SAMPLE
, <<<'CODE_SAMPLE'
(new SomeClass)->process(function (string $parameter) {});
CODE_SAMPLE
, [new AddParamTypeForFunctionLikeWithinCallLikeArgDeclaration('SomeClass', 'process', 0, 0, new StringType())])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param CallLike $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated as too specific and not practical for general core rules', self::class));
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, AddParamTypeForFunctionLikeWithinCallLikeArgDeclaration::class);
    }
}
