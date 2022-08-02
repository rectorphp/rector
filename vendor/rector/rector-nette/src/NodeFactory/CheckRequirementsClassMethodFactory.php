<?php

declare (strict_types=1);
namespace Rector\Nette\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix202208\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use RectorPrefix202208\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder;
final class CheckRequirementsClassMethodFactory
{
    /**
     * @var string
     */
    private const CHECK_REQUIREMENTS_METHOD_NAME = 'checkRequirements';
    /**
     * @readonly
     * @var \Rector\Nette\NodeFactory\ParentGetterStmtsToExternalStmtsFactory
     */
    private $parentGetterStmtsToExternalStmtsFactory;
    public function __construct(\Rector\Nette\NodeFactory\ParentGetterStmtsToExternalStmtsFactory $parentGetterStmtsToExternalStmtsFactory)
    {
        $this->parentGetterStmtsToExternalStmtsFactory = $parentGetterStmtsToExternalStmtsFactory;
    }
    /**
     * @param Node[] $getUserStmts
     */
    public function create(array $getUserStmts) : ClassMethod
    {
        $methodBuilder = new MethodBuilder(self::CHECK_REQUIREMENTS_METHOD_NAME);
        $methodBuilder->makePublic();
        $paramBuilder = new ParamBuilder('element');
        $methodBuilder->addParam($paramBuilder);
        $methodBuilder->setReturnType('void');
        $parentStaticCall = $this->creatParentStaticCall();
        $newStmts = $this->parentGetterStmtsToExternalStmtsFactory->create($getUserStmts);
        $methodBuilder->addStmts($newStmts);
        $methodBuilder->addStmt($parentStaticCall);
        return $methodBuilder->getNode();
    }
    private function creatParentStaticCall() : StaticCall
    {
        $args = [new Arg(new Variable('element'))];
        return new StaticCall(new Name('parent'), new Identifier(self::CHECK_REQUIREMENTS_METHOD_NAME), $args);
    }
}
