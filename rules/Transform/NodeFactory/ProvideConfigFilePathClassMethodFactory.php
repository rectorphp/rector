<?php

declare (strict_types=1);
namespace Rector\Transform\NodeFactory;

use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\NodeFactory;
final class ProvideConfigFilePathClassMethodFactory
{
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    public function create() : \PhpParser\Node\Stmt\ClassMethod
    {
        $classMethod = $this->nodeFactory->createPublicMethod('provideConfigFilePath');
        $classMethod->returnType = new \PhpParser\Node\Identifier('string');
        $concat = new \PhpParser\Node\Expr\BinaryOp\Concat(new \PhpParser\Node\Scalar\MagicConst\Dir(), new \PhpParser\Node\Scalar\String_('/config/configured_rule.php'));
        $return = new \PhpParser\Node\Stmt\Return_($concat);
        $classMethod->stmts[] = $return;
        return $classMethod;
    }
}
