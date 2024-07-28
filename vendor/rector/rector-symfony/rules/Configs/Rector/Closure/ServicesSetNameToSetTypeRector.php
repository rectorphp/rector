<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated. It does not have context about rest of application, will create invalid code.
 */
final class ServicesSetNameToSetTypeRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change $services->set("name_type", SomeType::class) to bare type, useful since Symfony 3.4', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('some_name', App\SomeClass::class);
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(App\SomeClass::class);
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        return null;
    }
}
