<?php

declare (strict_types=1);
namespace Rector\MysqlToMysqli\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://stackoverflow.com/a/34041762/1348344
 * @see \Rector\Tests\MysqlToMysqli\Rector\FuncCall\MysqlPConnectToMysqliConnectRector\MysqlPConnectToMysqliConnectRectorTest
 */
final class MysqlPConnectToMysqliConnectRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace mysql_pconnect() with mysqli_connect() with host p: prefix', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($host, $username, $password)
    {
        return mysql_pconnect($host, $username, $password);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($host, $username, $password)
    {
        return mysqli_connect('p:' . $host, $username, $password);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node, 'mysql_pconnect')) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        if (!$node->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $node->name = new \PhpParser\Node\Name('mysqli_connect');
        $node->args[0]->value = $this->joinStringWithNode('p:', $node->args[0]->value);
        return $node;
    }
    /**
     * @return \PhpParser\Node\Scalar\String_|\PhpParser\Node\Expr\BinaryOp\Concat
     */
    private function joinStringWithNode(string $string, \PhpParser\Node\Expr $expr)
    {
        if ($expr instanceof \PhpParser\Node\Scalar\String_) {
            return new \PhpParser\Node\Scalar\String_($string . $expr->value);
        }
        return new \PhpParser\Node\Expr\BinaryOp\Concat(new \PhpParser\Node\Scalar\String_($string), $expr);
    }
}
