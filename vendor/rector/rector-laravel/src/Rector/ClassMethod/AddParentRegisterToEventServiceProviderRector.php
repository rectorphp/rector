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
 * @changelog https://laravel.com/docs/8.x/upgrade#the-event-service-provider-class
 *
 * @see \Rector\Laravel\Tests\Rector\ClassMethod\AddParentRegisterToEventServiceProviderRector\AddParentRegisterToEventServiceProviderRectorTest
 */
final class AddParentRegisterToEventServiceProviderRector extends AbstractRector
{
    /**
     * @var string
     */
    private const REGISTER = 'register';
    /**
     * @readonly
     * @var \Rector\Nette\NodeAnalyzer\StaticCallAnalyzer
     */
    private $staticCallAnalyzer;
    public function __construct(StaticCallAnalyzer $staticCallAnalyzer)
    {
        $this->staticCallAnalyzer = $staticCallAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add parent::register(); call to register() class method in child of Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider', [new CodeSample(<<<'CODE_SAMPLE'
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function register()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function register()
    {
        parent::register();
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        $classLike = $this->betterNodeFinder->findParentType($node, ClassLike::class);
        if (!$classLike instanceof ClassLike) {
            return null;
        }
        if (!$this->isObjectType($classLike, new ObjectType('Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider'))) {
            return null;
        }
        if (!$this->isName($node->name, self::REGISTER)) {
            return null;
        }
        foreach ((array) $node->stmts as $key => $classMethodStmt) {
            if ($classMethodStmt instanceof Expression) {
                $classMethodStmt = $classMethodStmt->expr;
            }
            if (!$this->staticCallAnalyzer->isParentCallNamed($classMethodStmt, self::REGISTER)) {
                continue;
            }
            if ($key === 0) {
                return null;
            }
            unset($node->stmts[$key]);
        }
        $staticCall = $this->nodeFactory->createStaticCall('parent', self::REGISTER);
        $parentStaticCallExpression = new Expression($staticCall);
        $node->stmts = \array_merge([$parentStaticCallExpression], (array) $node->stmts);
        return $node;
    }
}
