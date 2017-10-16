<?php declare(strict_types=1);

namespace Rector\Builder\Contrib\Nette;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Node\NodeFactory;

final class RouterFactoryClassBuilder
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(BuilderFactory $builderFactory, NodeFactory $nodeFactory)
    {
        $this->builderFactory = $builderFactory;
        $this->nodeFactory = $nodeFactory;
    }

    /**
     * @param ArrayDimFetch[] $arrayDimFetchNodes
     * @return Node[]
     */
    public function build(array $arrayDimFetchNodes): array
    {
        $allNodes = [];

        $allNodes[] = $this->nodeFactory->createDeclareStrictTypes();

        $allNodes[] = $this->builderFactory->namespace('App\Routing\RouterFactory')
            ->getNode();

        $allNodes[] = $this->builderFactory->use('Nette\Application\Routers\Route')
            ->getNode();

        $allNodes[] = $this->builderFactory->use('Nette\Application\Routers\RouterList')
            ->getNode();

        $classBuild = $this->builderFactory->class('RouterFactory')
            ->makeFinal();

        $classMethodNode = $this->buildMethod($arrayDimFetchNodes);

        $classBuild->addStmts([$classMethodNode]);

        $allNodes[] = $classBuild->getNode();

        return $allNodes;
    }

    /**
     * @param New_[] $newRouteNodes
     */
    private function buildMethod(array $newRouteNodes): ClassMethod
    {
        $routerVariableNode = new Variable('router');

        $methodStatements = [];

        // adds "$router = new RouterList;"
        $methodStatements[] = new Assign($routerVariableNode, new New_(new Name('RouterList')));

        // adds routes $router[] = new ...
        foreach ($newRouteNodes as $newRouteNode) {
            $methodStatements[] = new Assign(
                new ArrayDimFetch(
                    $routerVariableNode
                ),
                $newRouteNode
            );
        }

        // adds "return $router;"
        $methodStatements[] = new Return_($routerVariableNode);

        $methodBuild = $this->builderFactory->method('create')
            ->makePublic()
            ->addStmts($methodStatements)
            ->setReturnType('RouterList');

        return $methodBuild->getNode();
    }
}
