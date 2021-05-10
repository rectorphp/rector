<?php

declare (strict_types=1);
namespace Rector\NetteToSymfony\NodeFactory;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\NodeFactory;
final class BuildFormClassMethodFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    public function create(\PhpParser\Node\Expr\Variable $formBuilderVariable) : \PhpParser\Node\Stmt\ClassMethod
    {
        $buildFormClassMethod = $this->nodeFactory->createPublicMethod('buildForm');
        $buildFormClassMethod->params[] = new \PhpParser\Node\Param($formBuilderVariable, null, new \PhpParser\Node\Name\FullyQualified('Symfony\\Component\\Form\\FormBuilderInterface'));
        $buildFormClassMethod->params[] = new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable('options'), null, new \PhpParser\Node\Identifier('array'));
        return $buildFormClassMethod;
    }
}
