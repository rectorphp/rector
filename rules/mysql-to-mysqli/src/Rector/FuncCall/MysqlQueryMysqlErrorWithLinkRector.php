<?php

declare(strict_types=1);

namespace Rector\MysqlToMysqli\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://www.php.net/manual/en/mysqli.error.php
 * @see https://www.php.net/manual/en/mysqli.query.php
 *
 * @see \Rector\MysqlToMysqli\Tests\Rector\FuncCall\MysqlQueryMysqlErrorWithLinkRector\MysqlQueryMysqlErrorWithLinkRectorTest
 */
final class MysqlQueryMysqlErrorWithLinkRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const FUNCTION_RENAME_MAP = [
        'mysql_error' => 'mysqli_error',
        'mysql_query' => 'mysqli_query',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add mysql_query and mysql_error with connection', [
            new CodeSample(
                <<<'PHP'
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
PHP
,
                <<<'PHP'
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
PHP

            ),
        ]);
    }

    /**
     * @return string[]
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
        $connectionVariable = $this->findConnectionVariable($node);

        // no connection? → unable to refactor
        if ($connectionVariable === null) {
            return null;
        }

        foreach (self::FUNCTION_RENAME_MAP as $oldFunction => $newFunction) {
            if (! $this->isName($node, $oldFunction)) {
                continue;
            }

            $node->name = new Name($newFunction);
            $node->args = array_merge([new Arg($connectionVariable)], $node->args);
        }

        return $node;
    }

    private function findConnectionVariable(FuncCall $funcCall): ?Expr
    {
        $connectionAssign = $this->betterNodeFinder->findFirstPrevious($funcCall, function (Node $node) {
            if (! $node instanceof Assign) {
                return null;
            }

            $staticType = $this->getStaticType($node);
            if ($staticType instanceof UnionType) {
                if ($staticType->isSuperTypeOf(new ObjectType('mysqli'))) {
                    return true;
                }

                if ($staticType->isSuperTypeOf(new ObjectType('mysql'))) {
                    return true;
                }
            }

            return false;
        });

        return $connectionAssign !== null ? $connectionAssign->var : null;
    }
}
