<?php

declare (strict_types=1);
namespace Rector\Doctrine\Dbal211\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\Dbal211\Rector\MethodCall\ReplaceFetchAllMethodCallRector\ReplaceFetchAllMethodCallRectorTest
 *
 * @changelog https://github.com/doctrine/dbal/pull/4019
 */
final class ReplaceFetchAllMethodCallRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change Doctrine\\DBAL\\Connection and Doctrine\\DBAL\\Driver\\ResultStatement ->fetchAll() to ->fetchAllAssociative() and other replacements', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\DBAL\Connection;

class SomeClass
{
    public function run(Connection $connection)
    {
        return $connection->fetchAll();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\DBAL\Connection;

class SomeClass
{
    public function run(Connection $connection)
    {
        return $connection->fetchAllAssociative();
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->isObjectType($node->var, new ObjectType('Doctrine\\DBAL\\Connection'))) {
            return $this->refactorConnection($node);
        }
        if ($this->isObjectType($node->var, new ObjectType('Doctrine\\DBAL\\Driver\\ResultStatement'))) {
            return $this->refactorResultStatement($node);
        }
        return null;
    }
    private function refactorConnection(MethodCall $methodCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        if ($this->isName($methodCall->name, 'fetchAll')) {
            $methodCall->name = new Identifier('fetchAllAssociative');
            return $methodCall;
        }
        if ($this->isName($methodCall->name, 'fetchArray')) {
            $methodCall->name = new Identifier('fetchNumeric');
            return $methodCall;
        }
        return null;
    }
    private function refactorResultStatement(MethodCall $methodCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        if ($this->isName($methodCall->name, 'fetchColumn')) {
            $methodCall->name = new Identifier('fetchOne');
            return $methodCall;
        }
        if ($this->isName($methodCall->name, 'fetchAll')) {
            $args = $methodCall->getArgs();
            if ($args === []) {
                // not sure yet
                return null;
            }
            $firstArg = $args[0];
            $newMethodName = $this->resolveFirstMethodName($firstArg);
            if (\is_string($newMethodName)) {
                $methodCall->args = [];
                $methodCall->name = new Identifier($newMethodName);
                return $methodCall;
            }
        }
        return null;
    }
    private function resolveFirstMethodName(Arg $firstArg) : ?string
    {
        if (!$firstArg->value instanceof ClassConstFetch) {
            return null;
        }
        $classConstFetch = $firstArg->value;
        if (!$this->isName($classConstFetch->class, 'PDO')) {
            return null;
        }
        if ($this->isName($classConstFetch->name, 'FETCH_COLUMN')) {
            return 'fetchFirstColumn';
        }
        if ($this->isName($classConstFetch->name, 'FETCH_ASSOC')) {
            return 'fetchAllAssociative';
        }
        return null;
    }
}
