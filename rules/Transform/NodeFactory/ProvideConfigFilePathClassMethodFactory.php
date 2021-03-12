<?php

declare(strict_types=1);

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
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    public function create(): ClassMethod
    {
        $classMethod = $this->nodeFactory->createPublicMethod('provideConfigFilePath');
        $classMethod->returnType = new Identifier('string');

        $concat = new Concat(new Dir(), new String_('/config/configured_rule.php'));
        $return = new Return_($concat);

        $classMethod->stmts[] = $return;

        return $classMethod;
    }
}
