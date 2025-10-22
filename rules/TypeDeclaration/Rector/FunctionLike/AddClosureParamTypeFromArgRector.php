<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\ValueObject\AddClosureParamTypeFromArg;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202510\Webmozart\Assert\Assert;
/**
 * @deprecated as too specific and not useful in Rector core. Implement it locally instead if needed.
 */
final class AddClosureParamTypeFromArgRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add closure param type based on known passed service/string types of method calls', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$app = new Container();
$app->extend(SomeClass::class, function ($parameter) {});
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$app = new Container();
$app->extend(SomeClass::class, function (SomeClass $parameter) {});
CODE_SAMPLE
, [new AddClosureParamTypeFromArg('Container', 'extend', 1, 0)])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
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
        Assert::allIsAOf($configuration, AddClosureParamTypeFromArg::class);
    }
}
