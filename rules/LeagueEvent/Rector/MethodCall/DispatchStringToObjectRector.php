<?php

declare (strict_types=1);
namespace Rector\LeagueEvent\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\LeagueEvent\Rector\MethodCall\DispatchStringToObjectRector\DispatchStringToObjectRectorTest
 */
final class DispatchStringToObjectRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const STMTS = 'stmts';
    /**
     * @var string
     */
    private const NAME = 'name';
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change string events to anonymous class which implement \\League\\Event\\HasEventName', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /** @var \League\Event\EventDispatcher */
    private $dispatcher;

    public function run()
    {
        $this->dispatcher->dispatch('my-event');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /** @var \League\Event\EventDispatcher */
    private $dispatcher;

    public function run()
    {
        $this->dispatcher->dispatch(new class implements \League\Event\HasEventName
        {
            public function eventName(): string
            {
                return 'my-event';
            }
        });
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Expr>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        return $this->updateNode($node);
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if (!$this->isNames($methodCall->name, ['dispatch', 'emit'])) {
            return \true;
        }
        if (!$this->nodeTypeResolver->isObjectTypes($methodCall->var, [new \PHPStan\Type\ObjectType('League\\Event\\EventDispatcher'), new \PHPStan\Type\ObjectType('League\\Event\\Emitter')])) {
            return \true;
        }
        if (!isset($methodCall->args[0])) {
            return \true;
        }
        if (!$methodCall->args[0] instanceof \PhpParser\Node\Arg) {
            return \true;
        }
        return !$this->getType($methodCall->args[0]->value) instanceof \PHPStan\Type\StringType;
    }
    private function updateNode(\PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node\Expr\MethodCall
    {
        /** @var Arg $firstArg */
        $firstArg = $methodCall->args[0];
        $methodCall->args[0] = new \PhpParser\Node\Arg($this->createNewAnonymousEventClass($firstArg->value));
        return $methodCall;
    }
    private function createNewAnonymousEventClass(\PhpParser\Node\Expr $expr) : \PhpParser\Node\Expr\New_
    {
        $implements = [new \PhpParser\Node\Name\FullyQualified('League\\Event\\HasEventName')];
        return new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Stmt\Class_(null, ['implements' => $implements, self::STMTS => $this->createAnonymousEventClassBody()]), [new \PhpParser\Node\Arg($expr)]);
    }
    /**
     * @return Stmt[]
     */
    private function createAnonymousEventClassBody() : array
    {
        $return = new \PhpParser\Node\Stmt\Return_(new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), 'name'));
        return [new \PhpParser\Node\Stmt\Property(\PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE, [new \PhpParser\Node\Stmt\PropertyProperty(self::NAME)]), new \PhpParser\Node\Stmt\ClassMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT, ['flags' => \PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC, 'params' => $this->createConstructParams(), self::STMTS => [new \PhpParser\Node\Stmt\Expression($this->createConstructAssign())]]), new \PhpParser\Node\Stmt\ClassMethod('eventName', ['flags' => \PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC, 'returnType' => 'string', self::STMTS => [$return]])];
    }
    /**
     * @return Param[]
     */
    private function createConstructParams() : array
    {
        return [new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable(self::NAME), null, 'string')];
    }
    private function createConstructAssign() : \PhpParser\Node\Expr\Assign
    {
        $propertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), 'name');
        return new \PhpParser\Node\Expr\Assign($propertyFetch, new \PhpParser\Node\Expr\Variable(self::NAME));
    }
}
