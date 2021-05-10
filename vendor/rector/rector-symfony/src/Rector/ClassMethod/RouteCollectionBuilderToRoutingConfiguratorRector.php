<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/pull/32937/files
 *
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector\RouteCollectionBuilderToRoutingConfiguratorRectorTest
 */
final class RouteCollectionBuilderToRoutingConfiguratorRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change RouteCollectionBuilder to RoutingConfiguratorRector', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node, 'configureRoutes')) {
            return null;
        }
        $firstParam = $node->params[0];
        if ($firstParam->type === null) {
            return null;
        }
        if (!$this->isName($firstParam->type, 'Symfony\\Component\\Routing\\RouteCollectionBuilder')) {
            return null;
        }
        $firstParam->type = new \PhpParser\Node\Name\FullyQualified('Symfony\\Component\\Routing\\Loader\\Configurator\\RoutingConfigurator');
        $node->name = new \PhpParser\Node\Identifier('configureRouting');
        $node->returnType = new \PhpParser\Node\Identifier('void');
        $this->traverseNodesWithCallable((array) $node->stmts, function (\PhpParser\Node $node) : ?MethodCall {
            if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
                return null;
            }
            if (!$this->isName($node->name, 'add')) {
                return null;
            }
            // avoid nesting chain iteration infinity loop
            $shouldSkip = (bool) $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::DO_NOT_CHANGE);
            if ($shouldSkip) {
                return null;
            }
            $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::DO_NOT_CHANGE, \true);
            $pathValue = $node->args[0]->value;
            $controllerValue = $node->args[1]->value;
            $nameValue = $node->args[2]->value ?? null;
            if (!$nameValue instanceof \PhpParser\Node\Expr) {
                throw new \Rector\Core\Exception\NotImplementedYetException();
            }
            $node->args = [new \PhpParser\Node\Arg($nameValue), new \PhpParser\Node\Arg($pathValue)];
            return new \PhpParser\Node\Expr\MethodCall($node, 'controller', [new \PhpParser\Node\Arg($controllerValue)]);
        });
        return $node;
    }
}
