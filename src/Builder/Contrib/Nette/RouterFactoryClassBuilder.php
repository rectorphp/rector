<?php declare(strict_types=1);

namespace Rector\Builder\Contrib\Nette;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Return_;

final class RouterFactoryClassBuilder
{
    /**
     * @var string
     */
    private const ROUTER_LIST_CLASS = 'Nette\Application\Routers\RouterList';

    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    public function __construct(BuilderFactory $builderFactory)
    {
        $this->builderFactory = $builderFactory;
    }

    /**
     * @param ArrayDimFetch[] $arrayDimFetchNodes
     */
    public function build(array $arrayDimFetchNodes): Class_
    {
        $classBuild = $this->builderFactory->class('App\RouterFactory');

        $routerVariable = new Variable('router');

        // adds "$router = new RouterList;"
        $methodStatements[] = new Assign($routerVariable, new New_(new Name(self::ROUTER_LIST_CLASS)));
        // adds routes
        $methodStatements = array_merge($methodStatements, $arrayDimFetchNodes);
        // adds "return $router;"
        $methodStatements[] = new Return_($routerVariable);

        $methodBuild = $this->builderFactory->method('create')
            ->addStmts($methodStatements)
            ->setReturnType(self::ROUTER_LIST_CLASS);

        $classBuild->addStmts([$methodBuild]);

        return $classBuild->getNode();
    }
}
