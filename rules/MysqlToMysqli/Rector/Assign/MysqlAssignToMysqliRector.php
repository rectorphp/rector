<?php

declare (strict_types=1);
namespace Rector\MysqlToMysqli\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.phpclasses.org/blog/package/9199/post/3-Smoothly-Migrate-your-PHP-Code-using-the-Old-MySQL-extension-to-MySQLi.html
 * @see \Rector\Tests\MysqlToMysqli\Rector\Assign\MysqlAssignToMysqliRector\MysqlAssignToMysqliRectorTest
 */
final class MysqlAssignToMysqliRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const FIELD_TO_FIELD_DIRECT = ['mysql_field_len' => 'length', 'mysql_field_name' => 'name', 'mysql_field_table' => 'table'];
    /**
     * @var string
     */
    private const MYSQLI_DATA_SEEK = 'mysqli_data_seek';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Converts more complex mysql functions to mysqli', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Expression::class];
    }
    /**
     * @param Expression $node
     */
    public function refactor(Node $node)
    {
        if (!$node->expr instanceof Assign) {
            return null;
        }
        $assign = $node->expr;
        if (!$assign->expr instanceof FuncCall) {
            return null;
        }
        $funcCall = $assign->expr;
        if ($this->isName($funcCall, 'mysql_tablename')) {
            return $this->processMysqlTableName($assign, $funcCall);
        }
        if ($this->isName($funcCall, 'mysql_db_name')) {
            return $this->processMysqlDbName($assign, $funcCall);
        }
        if ($this->isName($funcCall, 'mysql_db_query')) {
            return $this->processMysqliSelectDb($assign, $funcCall);
        }
        if ($this->isName($funcCall, 'mysql_fetch_field')) {
            $this->processMysqlFetchField($funcCall);
            return $node;
        }
        if ($this->isName($funcCall, 'mysql_result')) {
            return $this->processMysqlResult($assign, $funcCall);
        }
        return $this->processFieldToFieldDirect($assign, $funcCall);
    }
    /**
     * @return Stmt[]
     */
    private function processMysqlTableName(Assign $assign, FuncCall $funcCall) : array
    {
        $funcCall->name = new Name(self::MYSQLI_DATA_SEEK);
        $mysqlFetchArrayFuncCall = new FuncCall(new Name('mysql_fetch_array'), [$funcCall->args[0]]);
        $mysqlFetchArrayAssign = new Assign($assign->var, new ArrayDimFetch($mysqlFetchArrayFuncCall, new LNumber(0)));
        return [new Expression($funcCall), new Expression($mysqlFetchArrayAssign)];
    }
    /**
     * @return Stmt[]
     */
    private function processMysqlDbName(Assign $assign, FuncCall $mysqliDataSeekFuncCall) : array
    {
        $mysqliDataSeekFuncCall->name = new Name(self::MYSQLI_DATA_SEEK);
        $mysqliFetchRowFuncCall = new FuncCall(new Name('mysqli_fetch_row'), [$mysqliDataSeekFuncCall->args[0]]);
        $fetchVariable = new Variable('fetch');
        $mysqliFetchRowAssign = new Assign($fetchVariable, $mysqliFetchRowFuncCall);
        $fetchAssig = new Assign($assign->var, new ArrayDimFetch($fetchVariable, new LNumber(0)));
        return [new Expression($mysqliDataSeekFuncCall), new Expression($mysqliFetchRowAssign), new Expression($fetchAssig)];
    }
    /**
     * @return Stmt[]
     */
    private function processMysqliSelectDb(Assign $assign, FuncCall $funcCall) : array
    {
        $funcCall->name = new Name('mysqli_select_db');
        $mysqliQueryAssign = new Assign($assign->var, new FuncCall(new Name('mysqli_query'), [$funcCall->args[1]]));
        unset($funcCall->args[1]);
        return [new Expression($funcCall), new Expression($mysqliQueryAssign)];
    }
    private function processMysqlFetchField(FuncCall $funcCall) : void
    {
        $hasExactField = isset($funcCall->args[1]);
        $funcCall->name = new Name($hasExactField ? 'mysqli_fetch_field_direct' : 'mysqli_fetch_field');
    }
    /**
     * @return Stmt[]
     */
    private function processMysqlResult(Assign $assign, FuncCall $funcCall) : array
    {
        $fetchField = null;
        if (isset($funcCall->args[2]) && $funcCall->args[2] instanceof Arg) {
            $fetchField = $funcCall->args[2]->value;
            unset($funcCall->args[2]);
        }
        $funcCall->name = new Name(self::MYSQLI_DATA_SEEK);
        $mysqlFetchArrayFuncCall = new FuncCall(new Name('mysqli_fetch_array'), [$funcCall->args[0]]);
        $fetchVariable = new Variable('fetch');
        $mysqlFetchArrayAssign = new Assign($fetchVariable, $mysqlFetchArrayFuncCall);
        $fetchAssign = new Assign($assign->var, new ArrayDimFetch($fetchVariable, $fetchField ?? new LNumber(0)));
        return [new Expression($funcCall), new Expression($mysqlFetchArrayAssign), new Expression($fetchAssign)];
    }
    private function processFieldToFieldDirect(Assign $assign, FuncCall $funcCall) : ?Assign
    {
        foreach (self::FIELD_TO_FIELD_DIRECT as $funcName => $property) {
            if (!$this->isName($funcCall, $funcName)) {
                continue;
            }
            $funcCall->name = new Name('mysqli_fetch_field_direct');
            $assign->expr = new PropertyFetch($funcCall, $property);
            return $assign;
        }
        return null;
    }
}
