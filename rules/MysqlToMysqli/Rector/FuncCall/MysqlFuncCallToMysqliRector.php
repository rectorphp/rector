<?php

declare(strict_types=1);

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
 * @changelog https://www.phpclasses.org/blog/package/9199/post/3-Smoothly-Migrate-your-PHP-Code-using-the-Old-MySQL-extension-to-MySQLi.html
 *
 * @see \Rector\Tests\MysqlToMysqli\Rector\FuncCall\MysqlFuncCallToMysqliRector\MysqlFuncCallToMysqliRectorTest
 */
final class MysqlFuncCallToMysqliRector extends AbstractRector
{
    /**
     * @var string
     */
    private const MYSQLI_QUERY = 'mysqli_query';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts more complex mysql functions to mysqli',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
mysql_drop_db($database);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
mysqli_query('DROP DATABASE ' . $database);
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isName($node, 'mysql_create_db')) {
            return $this->processMysqlCreateDb($node);
        }

        if ($this->isName($node, 'mysql_drop_db')) {
            return $this->processMysqlDropDb($node);
        }

        if ($this->isName($node, 'mysql_list_dbs')) {
            $node->name = new Name(self::MYSQLI_QUERY);
            $node->args[0] = new Arg(new String_('SHOW DATABASES'));
        }

        if ($this->isName($node, 'mysql_list_fields')) {
            $node->name = new Name(self::MYSQLI_QUERY);
            $node->args[0]->value = $this->joinStringWithNode('SHOW COLUMNS FROM', $node->args[1]->value);

            unset($node->args[1]);
        }

        if ($this->isName($node, 'mysql_list_tables')) {
            $node->name = new Name(self::MYSQLI_QUERY);
            $node->args[0]->value = $this->joinStringWithNode('SHOW TABLES FROM', $node->args[0]->value);
        }

        return $node;
    }

    private function processMysqlCreateDb(FuncCall $funcCall): FuncCall
    {
        $funcCall->name = new Name(self::MYSQLI_QUERY);
        $funcCall->args[0]->value = $this->joinStringWithNode('CREATE DATABASE', $funcCall->args[0]->value);

        return $funcCall;
    }

    private function processMysqlDropDb(FuncCall $funcCall): FuncCall
    {
        $funcCall->name = new Name(self::MYSQLI_QUERY);
        $funcCall->args[0]->value = $this->joinStringWithNode('DROP DATABASE', $funcCall->args[0]->value);

        return $funcCall;
    }

    private function joinStringWithNode(string $string, Expr $expr): String_ | Concat
    {
        if ($expr instanceof String_) {
            return new String_($string . ' ' . $expr->value);
        }

        return new Concat(new String_($string . ' '), $expr);
    }
}
