<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\PhpVersionFeature;
final class BareLogoutClassMethodFactory
{
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->nodeFactory = $nodeFactory;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function create() : \PhpParser\Node\Stmt\ClassMethod
    {
        $classMethod = $this->nodeFactory->createPublicMethod('onLogout');
        $variable = new \PhpParser\Node\Expr\Variable('logoutEvent');
        $classMethod->params[] = $this->createLogoutEventParam($variable);
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::VOID_TYPE)) {
            $classMethod->returnType = new \PhpParser\Node\Identifier('void');
        }
        return $classMethod;
    }
    private function createLogoutEventParam(\PhpParser\Node\Expr\Variable $variable) : \PhpParser\Node\Param
    {
        $param = new \PhpParser\Node\Param($variable);
        $param->type = new \PhpParser\Node\Name\FullyQualified('Symfony\\Component\\Security\\Http\\Event\\LogoutEvent');
        return $param;
    }
}
