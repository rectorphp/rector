<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/laravel/framework/pull/36906
 * @see https://github.com/laravel/framework/pull/37352
 *
 * @see \Rector\Laravel\Tests\Rector\Class_\AnonymousMigrationsRector\AnonymousMigrationsRectorTest
 */
final class AnonymousMigrationsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    public function __construct(\Rector\Core\NodeAnalyzer\ClassAnalyzer $classAnalyzer)
    {
        $this->classAnalyzer = $classAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert migrations to anonymous classes.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    // ...
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // ...
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node, new \PHPStan\Type\ObjectType('Illuminate\\Database\\Migrations\\Migration'))) {
            return null;
        }
        if ($this->classAnalyzer->isAnonymousClass($node)) {
            return null;
        }
        return new \PhpParser\Node\Stmt\Return_(new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Stmt\Class_(null, ['flags' => $node->flags, 'extends' => $node->extends, 'implements' => $node->implements, 'stmts' => $node->stmts, 'attrGroups' => $node->attrGroups])));
    }
}
