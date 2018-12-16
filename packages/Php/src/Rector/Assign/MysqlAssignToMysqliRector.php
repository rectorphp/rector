<?php declare(strict_types=1);

namespace Rector\Php\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://www.phpclasses.org/blog/package/9199/post/3-Smoothly-Migrate-your-PHP-Code-using-the-Old-MySQL-extension-to-MySQLi.html
 */
final class MysqlAssignToMysqliRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $fieldToFieldDirect = [
        'mysql_field_len' => 'length',
        'mysql_field_name' => 'name',
        'mysql_field_table' => 'table',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Converts more complex mysql functions to mysqli',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$data = mysql_db_name($result, $row);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
mysqli_data_seek($result, $row);
$fetch = mysql_fetch_row($result);
$data = $fetch[0];
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof FuncCall) {
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

        return $this->processFieldToFieldDirect($node, $funcCallNode);
    }

    private function processMysqlTableName(Assign $assignNode, FuncCall $funcCall): FuncCall
    {
        $funcCall->name = new Name('mysqli_data_seek');

        $newFuncCall = new FuncCall(new Name('mysql_fetch_array'), [$funcCall->args[0]]);
        $newAssignNode = new Assign($assignNode->var, new ArrayDimFetch($newFuncCall, new LNumber(0)));

        $this->addNodeAfterNode($newAssignNode, $assignNode);

        return $funcCall;
    }

    private function processMysqlDbName(Assign $assignNode, FuncCall $funcCallNode): FuncCall
    {
        $funcCallNode->name = new Name('mysqli_data_seek');

        $mysqlFetchRowFuncCall = new FuncCall(new Name('mysql_fetch_row'), [$funcCallNode->args[0]]);
        $fetchVariable = new Variable('fetch');
        $newAssignNode = new Assign($fetchVariable, $mysqlFetchRowFuncCall);
        $this->addNodeAfterNode($newAssignNode, $assignNode);

        $newAssignNode = new Assign($assignNode->var, new ArrayDimFetch($fetchVariable, new LNumber(0)));
        $this->addNodeAfterNode($newAssignNode, $assignNode);

        return $funcCallNode;
    }

    private function processMysqliSelectDb(Assign $assignNode, FuncCall $funcCallNode): FuncCall
    {
        $funcCallNode->name = new Name('mysqli_select_db');

        $newAssignNode = new Assign($assignNode->var, new FuncCall(new Name('mysqli_query'), [$funcCallNode->args[1]]));
        $this->addNodeAfterNode($newAssignNode, $assignNode);

        unset($funcCallNode->args[1]);

        return $funcCallNode;
    }

    private function processMysqlFetchField(Assign $assignNode, FuncCall $funcCallNode): Assign
    {
        $funcCallNode->name = new Name('mysqli_fetch_field');

        if (! isset($funcCallNode->args[1])) {
            return $assignNode;
        }

        unset($funcCallNode->args[1]);

        // add for
        $xVar = new Variable('x');
        $forNode = new For_([
            'init' => [new Assign($xVar, new LNumber(0))],
            'cond' => [new Smaller($xVar, new LNumber(5))],
            'loop' => [new PostInc($xVar)],
            'stmts' => [new Expression($funcCallNode)],
        ]);

        $this->addNodeAfterNode($forNode, $assignNode->getAttribute(Attribute::PREVIOUS_EXPRESSION));

        return $assignNode;
    }

    private function processFieldToFieldDirect(Assign $assignNode, FuncCall $funcCallNode): ?Assign
    {
        foreach ($this->fieldToFieldDirect as $funcName => $property) {
            if ($this->isName($funcCallNode, $funcName)) {
                if ($funcCallNode->getAttribute(Attribute::PARENT_NODE) instanceof PropertyFetch) {
                    continue;
                }

                $funcCallNode->name = new Name('mysqli_fetch_field_direct');
                $assignNode->expr = new PropertyFetch($funcCallNode, $property);

                return $assignNode;
            }
        }

        return null;
    }
}
