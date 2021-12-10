<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Nette\NodeAnalyzer\StaticCallAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://laracasts.com/discuss/channels/laravel/laravel-57-upgrade-observer-problem
 *
 * @see \Rector\Laravel\Tests\Rector\ClassMethod\AddParentBootToModelClassMethodRector\AddParentBootToModelClassMethodRectorTest
 */
final class AddParentBootToModelClassMethodRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const BOOT = 'boot';
    /**
     * @var \Rector\Nette\NodeAnalyzer\StaticCallAnalyzer
     */
    private $staticCallAnalyzer;
    public function __construct(\Rector\Nette\NodeAnalyzer\StaticCallAnalyzer $staticCallAnalyzer)
    {
        $this->staticCallAnalyzer = $staticCallAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add parent::boot(); call to boot() class method in child of Illuminate\\Database\\Eloquent\\Model', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function boot()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function boot()
    {
        parent::boot();
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $classLike = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        if (!$this->isObjectType($classLike, new \PHPStan\Type\ObjectType('Illuminate\\Database\\Eloquent\\Model'))) {
            return null;
        }
        if (!$this->isName($node->name, self::BOOT)) {
            return null;
        }
        foreach ((array) $node->stmts as $key => $classMethodStmt) {
            if ($classMethodStmt instanceof \PhpParser\Node\Stmt\Expression) {
                $classMethodStmt = $classMethodStmt->expr;
            }
            // is in the 1st position? → only correct place
            // @see https://laracasts.com/discuss/channels/laravel/laravel-57-upgrade-observer-problem?page=0#reply=454409
            if (!$this->staticCallAnalyzer->isParentCallNamed($classMethodStmt, self::BOOT)) {
                continue;
            }
            if ($key === 0) {
                return null;
            }
            // wrong location → remove it
            unset($node->stmts[$key]);
        }
        // missing, we need to add one
        $staticCall = $this->nodeFactory->createStaticCall('parent', self::BOOT);
        $parentStaticCallExpression = new \PhpParser\Node\Stmt\Expression($staticCall);
        $node->stmts = \array_merge([$parentStaticCallExpression], (array) $node->stmts);
        return $node;
    }
}
