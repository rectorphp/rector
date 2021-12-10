<?php

declare (strict_types=1);
namespace Rector\MysqlToMysqli\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/mysqli.error.php
 *
 * @see \Rector\Tests\MysqlToMysqli\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector\MysqlQueryMysqlErrorWithLinkRectorTest
 */
final class MysqlQueryMysqlErrorWithLinkRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const FUNCTION_RENAME_MAP = ['mysql_affected_rows' => 'mysqli_affected_rows', 'mysql_client_encoding' => 'mysqli_character_set_name', 'mysql_close' => 'mysqli_close', 'mysql_errno' => 'mysqli_errno', 'mysql_error' => 'mysqli_error', 'mysql_escape_string' => 'mysqli_real_escape_string', 'mysql_get_host_info' => 'mysqli_get_host_info', 'mysql_get_proto_info' => 'mysqli_get_proto_info', 'mysql_get_server_info' => 'mysqli_get_server_info', 'mysql_info' => 'mysqli_info', 'mysql_insert_id' => 'mysqli_insert_id', 'mysql_ping' => 'mysqli_ping', 'mysql_query' => 'mysqli_query', 'mysql_real_escape_string' => 'mysqli_real_escape_string', 'mysql_select_db' => 'mysqli_select_db', 'mysql_set_charset' => 'mysqli_set_charset', 'mysql_stat' => 'mysqli_stat', 'mysql_thread_id' => 'mysqli_thread_id'];
    /**
     * @var array<string, int>
     */
    private const FUNCTION_CONNECTION_PARAMETER_POSITION_MAP = ['mysql_affected_rows' => 0, 'mysql_client_encoding' => 0, 'mysql_close' => 0, 'mysql_errno' => 0, 'mysql_error' => 0, 'mysql_get_host_info' => 0, 'mysql_get_proto_info' => 0, 'mysql_get_server_info' => 0, 'mysql_info' => 0, 'mysql_insert_id' => 0, 'mysql_ping' => 0, 'mysql_query' => 1, 'mysql_real_escape_string' => 1, 'mysql_select_db' => 1, 'mysql_set_charset' => 1, 'mysql_stat' => 0, 'mysql_thread_id' => 0];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add mysql_query and mysql_error with connection', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $conn = mysqli_connect('host', 'user', 'pass');

        mysql_error();
        $sql = 'SELECT';

        return mysql_query($sql);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $conn = mysqli_connect('host', 'user', 'pass');

        mysqli_error($conn);
        $sql = 'SELECT';

        return mysqli_query($conn, $sql);
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
        foreach (self::FUNCTION_RENAME_MAP as $oldFunction => $newFunction) {
            if (!$this->isName($node, $oldFunction)) {
                continue;
            }
            $args = $node->args;
            if ($args === [] || !$this->isProbablyMysql($args[0]->value)) {
                $connectionVariable = $this->findConnectionVariable($node);
                $this->removeExistingConnectionParameter($node);
                if (!$connectionVariable instanceof \PhpParser\Node\Expr) {
                    return null;
                }
                $node->args = \array_merge([new \PhpParser\Node\Arg($connectionVariable)], $node->args);
            }
            $node->name = new \PhpParser\Node\Name($newFunction);
            return $node;
        }
        return null;
    }
    private function isProbablyMysql(\PhpParser\Node\Expr $expr) : bool
    {
        if ($this->isObjectType($expr, new \PHPStan\Type\ObjectType('mysqli'))) {
            return \true;
        }
        $staticType = $this->getType($expr);
        $resourceType = new \PHPStan\Type\ResourceType();
        if ($staticType->equals($resourceType)) {
            return \true;
        }
        if ($this->isUnionTypeWithResourceSubType($staticType, $resourceType)) {
            return \true;
        }
        if (!$expr instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        return $this->isMysqliConnect($expr);
    }
    private function findConnectionVariable(\PhpParser\Node\Expr\FuncCall $funcCall) : ?\PhpParser\Node\Expr
    {
        $connectionAssign = $this->betterNodeFinder->findFirstPrevious($funcCall, function (\PhpParser\Node $node) : ?bool {
            if (!$node instanceof \PhpParser\Node\Expr\Assign) {
                return null;
            }
            return $this->isObjectType($node->expr, new \PHPStan\Type\ObjectType('mysqli'));
        });
        if (!$connectionAssign instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        return $connectionAssign->var;
    }
    private function removeExistingConnectionParameter(\PhpParser\Node\Expr\FuncCall $funcCall) : void
    {
        /** @var string $functionName */
        $functionName = $this->getName($funcCall);
        if (!isset(self::FUNCTION_CONNECTION_PARAMETER_POSITION_MAP[$functionName])) {
            return;
        }
        $connectionPosition = self::FUNCTION_CONNECTION_PARAMETER_POSITION_MAP[$functionName];
        unset($funcCall->args[$connectionPosition]);
    }
    private function isUnionTypeWithResourceSubType(\PHPStan\Type\Type $staticType, \PHPStan\Type\ResourceType $resourceType) : bool
    {
        if ($staticType instanceof \PHPStan\Type\UnionType) {
            foreach ($staticType->getTypes() as $type) {
                if ($type->equals($resourceType)) {
                    return \true;
                }
            }
        }
        return \false;
    }
    private function isMysqliConnect(\PhpParser\Node\Expr\Variable $variable) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstPrevious($variable, function (\PhpParser\Node $node) use($variable) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\Assign) {
                return \false;
            }
            if (!$node->expr instanceof \PhpParser\Node\Expr\FuncCall) {
                return \false;
            }
            if (!$this->nodeComparator->areNodesEqual($node->var, $variable)) {
                return \false;
            }
            return $this->isName($node->expr, 'mysqli_connect');
        });
    }
}
