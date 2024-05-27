<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony51\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Exception\NotImplementedYetException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony/pull/32937/files
 *
 * @see \Rector\Symfony\Tests\Symfony51\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector\RouteCollectionBuilderToRoutingConfiguratorRectorTest
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node, new ObjectType('Symfony\\Component\\HttpKernel\\Kernel'))) {
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
        if (!$this->isName($firstParam->type, 'Symfony\\Component\\Routing\\RouteCollectionBuilder')) {
            return null;
        }
        $firstParam->type = new FullyQualified('Symfony\\Component\\Routing\\Loader\\Configurator\\RoutingConfigurator');
        $configureRoutesClassMethod->name = new Identifier('configureRouting');
        $configureRoutesClassMethod->returnType = new Identifier('void');
        $this->traverseNodesWithCallable((array) $configureRoutesClassMethod->stmts, function (Node $node) : ?MethodCall {
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
