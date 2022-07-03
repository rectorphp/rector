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
 * @changelog https://github.com/laravel/framework/pull/36906
 * @changelog https://github.com/laravel/framework/pull/37352
 *
 * @see \Rector\Laravel\Tests\Rector\Class_\AnonymousMigrationsRector\AnonymousMigrationsRectorTest
 */
final class AnonymousMigrationsRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    public function __construct(ClassAnalyzer $classAnalyzer)
    {
        $this->classAnalyzer = $classAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert migrations to anonymous classes.', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node, new ObjectType('Illuminate\\Database\\Migrations\\Migration'))) {
            return null;
        }
        if ($node->isAbstract()) {
            return null;
        }
        if ($this->classAnalyzer->isAnonymousClass($node)) {
            return null;
        }
        return new Return_(new New_(new Class_(null, ['flags' => $node->flags, 'extends' => $node->extends, 'implements' => $node->implements, 'stmts' => $node->stmts, 'attrGroups' => $node->attrGroups])));
    }
}
