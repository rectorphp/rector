<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\Rector\Core\Php\PhpVersionProvider;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NodeFactory;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersionFeature;
final class BareLogoutClassMethodFactory
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
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
