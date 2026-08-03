<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony60\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyClass;
use Rector\Symfony\Enum\SymfonyFunctionName;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony/pull/42149
 *
 * @see \Rector\Symfony\Tests\Symfony60\Rector\FuncCall\ContainerInterfaceServiceToServiceContainerRector\ContainerInterfaceServiceToServiceContainerRectorTest
 */
final class ContainerInterfaceServiceToServiceContainerRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @var string[]
     */
    private const CONTAINER_INTERFACES = [SymfonyClass::PSR_CONTAINER_INTERFACE, SymfonyClass::DEPENDENCY_INJECTION_CONTAINER_INTERFACE];
    /**
     * @var string
     */
    private const SERVICE_CONTAINER = 'service_container';
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('symfony/dependency-injection', '>=6.0');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace removed ContainerInterface alias with "service_container" service id in service() call', [new CodeSample(<<<'CODE_SAMPLE'
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return service(ContainerInterface::class);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return service('service_container');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?FuncCall
    {
        if (!$this->isName($node->name, SymfonyFunctionName::SERVICE)) {
            return null;
        }
        $firstArg = $node->args[0] ?? null;
        if (!$firstArg instanceof Arg) {
            return null;
        }
        foreach (self::CONTAINER_INTERFACES as $containerInterface) {
            if (!$this->valueResolver->isValue($firstArg->value, $containerInterface)) {
                continue;
            }
            $node->args[0] = new Arg(new String_(self::SERVICE_CONTAINER));
            return $node;
        }
        return null;
    }
}
