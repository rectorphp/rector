<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\Rector\ClassMethod;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\Rector\Core\Exception\NotImplementedYetException;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/pull/32937/files
 *
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector\RouteCollectionBuilderToRoutingConfiguratorRectorTest
 */
final class RouteCollectionBuilderToRoutingConfiguratorRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
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
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
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
        $firstParam->type = new FullyQualified('Symfony\\Component\\Routing\\Loader\\Configurator\\RoutingConfigurator');
        $node->name = new Identifier('configureRouting');
        $node->returnType = new Identifier('void');
        $this->traverseNodesWithCallable((array) $node->stmts, function (Node $node) : ?MethodCall {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isName($node->name, 'add')) {
                return null;
            }
            // avoid nesting chain iteration infinity loop
            $shouldSkip = (bool) $node->getAttribute(AttributeKey::DO_NOT_CHANGE);
            if ($shouldSkip) {
                return null;
            }
            $node->setAttribute(AttributeKey::DO_NOT_CHANGE, \true);
            $pathValue = $node->getArgs()[0]->value;
            $controllerValue = $node->getArgs()[1]->value;
            $nameValue = $node->getArgs()[2]->value ?? null;
            if (!$nameValue instanceof Expr) {
                throw new NotImplementedYetException();
            }
            $node->args = [new Arg($nameValue), new Arg($pathValue)];
            return new MethodCall($node, 'controller', [new Arg($controllerValue)]);
        });
        return $node;
    }
}
