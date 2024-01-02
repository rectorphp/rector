<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Php\PhpVersionProvider;
use Rector\PhpParser\Node\NodeFactory;
use Rector\ValueObject\PhpVersionFeature;
final class BareLogoutClassMethodFactory
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(NodeFactory $nodeFactory, PhpVersionProvider $phpVersionProvider)
    {
        $this->nodeFactory = $nodeFactory;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function create() : ClassMethod
    {
        $classMethod = $this->nodeFactory->createPublicMethod('onLogout');
        $variable = new Variable('logoutEvent');
        $classMethod->params[] = $this->createLogoutEventParam($variable);
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::VOID_TYPE)) {
            $classMethod->returnType = new Identifier('void');
        }
        return $classMethod;
    }
    private function createLogoutEventParam(Variable $variable) : Param
    {
        $param = new Param($variable);
        $param->type = new FullyQualified('Symfony\\Component\\Security\\Http\\Event\\LogoutEvent');
        return $param;
    }
}
