<?php

declare (strict_types=1);
namespace Rector\MysqlToMysqli\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.phpclasses.org/blog/package/9199/post/3-Smoothly-Migrate-your-PHP-Code-using-the-Old-MySQL-extension-to-MySQLi.html
 * @see \Rector\Tests\MysqlToMysqli\Rector\Assign\MysqlAssignToMysqliRector\MysqlAssignToMysqliRectorTest
 */
final class MysqlAssignToMysqliRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const FIELD_TO_FIELD_DIRECT = ['mysql_field_len' => 'length', 'mysql_field_name' => 'name', 'mysql_field_table' => 'table'];
    /**
     * @var string
     */
    private const MYSQLI_DATA_SEEK = 'mysqli_data_seek';
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Converts more complex mysql functions to mysqli', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$data = mysql_db_name($result, $row);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
mysqli_data_seek($result, $row);
$fetch = mysql_fetch_row($result);
$data = $fetch[0];
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->expr instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        /** @var FuncCall $funcCallNode */
        $funcCallNode = $node->expr;
        if ($this->isName($funcCallNode, 'mysql_tablename')) {
            return $this->processMysqlTableName($node, $funcCallNode);
        }
        if ($this->isName($funcCallNode, 'mysql_db_name')) {
            return $this->processMysqlDbName($node, $funcCallNode);
        }
        if ($this->isName($funcCallNode, 'mysql_db_query')) {
            return $this->processMysqliSelectDb($node, $funcCallNode);
        }
        if ($this->isName($funcCallNode, 'mysql_fetch_field')) {
            return $this->processMysqlFetchField($node, $funcCallNode);
        }
        if ($this->isName($funcCallNode, 'mysql_result')) {
            return $this->processMysqlResult($node, $funcCallNode);
        }
        return $this->processFieldToFieldDirect($node, $funcCallNode);
    }
    private function processMysqlTableName(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Expr\FuncCall $funcCall) : \PhpParser\Node\Expr\FuncCall
    {
        $funcCall->name = new \PhpParser\Node\Name(self::MYSQLI_DATA_SEEK);
        $newFuncCall = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('mysql_fetch_array'), [$funcCall->args[0]]);
        $newAssignNode = new \PhpParser\Node\Expr\Assign($assign->var, new \PhpParser\Node\Expr\ArrayDimFetch($newFuncCall, new \PhpParser\Node\Scalar\LNumber(0)));
        $this->nodesToAddCollector->addNodeAfterNode($newAssignNode, $assign);
        return $funcCall;
    }
    private function processMysqlDbName(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Expr\FuncCall $funcCall) : \PhpParser\Node\Expr\FuncCall
    {
        $funcCall->name = new \PhpParser\Node\Name(self::MYSQLI_DATA_SEEK);
        $mysqlFetchRowFuncCall = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('mysqli_fetch_row'), [$funcCall->args[0]]);
        $fetchVariable = new \PhpParser\Node\Expr\Variable('fetch');
        $newAssignNode = new \PhpParser\Node\Expr\Assign($fetchVariable, $mysqlFetchRowFuncCall);
        $this->nodesToAddCollector->addNodeAfterNode($newAssignNode, $assign);
        $newAssignNodeAfter = new \PhpParser\Node\Expr\Assign($assign->var, new \PhpParser\Node\Expr\ArrayDimFetch($fetchVariable, new \PhpParser\Node\Scalar\LNumber(0)));
        $this->nodesToAddCollector->addNodeAfterNode($newAssignNodeAfter, $assign);
        return $funcCall;
    }
    private function processMysqliSelectDb(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Expr\FuncCall $funcCall) : \PhpParser\Node\Expr\FuncCall
    {
        $funcCall->name = new \PhpParser\Node\Name('mysqli_select_db');
        $newAssignNode = new \PhpParser\Node\Expr\Assign($assign->var, new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('mysqli_query'), [$funcCall->args[1]]));
        $this->nodesToAddCollector->addNodeAfterNode($newAssignNode, $assign);
        unset($funcCall->args[1]);
        return $funcCall;
    }
    private function processMysqlFetchField(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Expr\FuncCall $funcCall) : \PhpParser\Node\Expr\Assign
    {
        $funcCall->name = isset($funcCall->args[1]) ? new \PhpParser\Node\Name('mysqli_fetch_field_direct') : new \PhpParser\Node\Name('mysqli_fetch_field');
        return $assign;
    }
    private function processMysqlResult(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Expr\FuncCall $funcCall) : \PhpParser\Node\Expr\FuncCall
    {
        $fetchField = null;
        if (isset($funcCall->args[2]) && $funcCall->args[2] instanceof \PhpParser\Node\Arg) {
            $fetchField = $funcCall->args[2]->value;
            unset($funcCall->args[2]);
        }
        $funcCall->name = new \PhpParser\Node\Name(self::MYSQLI_DATA_SEEK);
        $mysqlFetchArrayFuncCall = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('mysqli_fetch_array'), [$funcCall->args[0]]);
        $fetchVariable = new \PhpParser\Node\Expr\Variable('fetch');
        $newAssignNode = new \PhpParser\Node\Expr\Assign($fetchVariable, $mysqlFetchArrayFuncCall);
        $this->nodesToAddCollector->addNodeAfterNode($newAssignNode, $assign);
        $newAssignNodeAfter = new \PhpParser\Node\Expr\Assign($assign->var, new \PhpParser\Node\Expr\ArrayDimFetch($fetchVariable, $fetchField ?? new \PhpParser\Node\Scalar\LNumber(0)));
        $this->nodesToAddCollector->addNodeAfterNode($newAssignNodeAfter, $assign);
        return $funcCall;
    }
    private function processFieldToFieldDirect(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Expr\FuncCall $funcCall) : ?\PhpParser\Node\Expr\Assign
    {
        foreach (self::FIELD_TO_FIELD_DIRECT as $funcName => $property) {
            if ($this->isName($funcCall, $funcName)) {
                $parentNode = $funcCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
                if ($parentNode instanceof \PhpParser\Node\Expr\PropertyFetch) {
                    continue;
                }
                if ($parentNode instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
                    continue;
                }
                $funcCall->name = new \PhpParser\Node\Name('mysqli_fetch_field_direct');
                $assign->expr = new \PhpParser\Node\Expr\PropertyFetch($funcCall, $property);
                return $assign;
            }
        }
        return null;
    }
}
