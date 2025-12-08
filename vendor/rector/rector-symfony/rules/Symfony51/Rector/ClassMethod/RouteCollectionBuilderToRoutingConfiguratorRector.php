<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony51\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony/pull/32937/files
 *
 * @see \Rector\Symfony\Tests\Symfony51\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector\RouteCollectionBuilderToRoutingConfiguratorRectorTest
 */
final class RouteCollectionBuilderToRoutingConfiguratorRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change RouteCollectionBuilder to RoutingConfiguratorRector', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class ConcreteMicroKernel extends Kernel
{
    use MicroKernelTrait;

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->add('/admin', 'App\Controller\AdminController::dashboard', 'admin_dashboard');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class ConcreteMicroKernel extends Kernel
{
    use MicroKernelTrait;

    protected function configureRouting(RoutingConfigurator $routes): void
    {
        $routes->add('admin_dashboard', '/admin')
            ->controller('App\Controller\AdminController::dashboard')
    }}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isObjectType($node, new ObjectType(SymfonyClass::KERNEL))) {
            return null;
        }
        $configureRoutesClassMethod = $node->getMethod('configureRoutes');
        if (!$configureRoutesClassMethod instanceof ClassMethod) {
            return null;
        }
        $firstParam = $configureRoutesClassMethod->params[0];
        if ($firstParam->type === null) {
            return null;
        }
        if (!$this->isName($firstParam->type, SymfonyClass::ROUTE_COLLECTION_BUILDER)) {
            return null;
        }
        $firstParam->type = new FullyQualified(SymfonyClass::ROUTING_CONFIGURATOR);
        $configureRoutesClassMethod->name = new Identifier('configureRouting');
        $configureRoutesClassMethod->returnType = new Identifier('void');
        $this->traverseNodesWithCallable((array) $configureRoutesClassMethod->stmts, function (Node $node): ?MethodCall {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isName($node->name, 'add')) {
                return null;
            }
            // already filled
            $args = $node->getArgs();
            if (count($args) === 2) {
                return null;
            }
            $pathValue = $args[0]->value;
            $controllerValue = $args[1]->value;
            $nameValue = $args[2]->value;
            $node->args = [new Arg($nameValue), new Arg($pathValue)];
            return new MethodCall($node, 'controller', [new Arg($controllerValue)]);
        });
        return $node;
    }
}
