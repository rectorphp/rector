<?php

namespace Rector\LeagueEvent\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\LeagueEvent\Rector\MethodCall\DispatchStringToObjectRector\DispatchStringToObjectRectorTest
 */
final class DispatchStringToObjectRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change string events to objects which implement \League\Event\HasEventName', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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
                ,
                <<<'CODE_SAMPLE'
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
            )
        ]);
    }

    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        return $this->updateNode($node);
    }

    private function shouldSkip(MethodCall $methodCall): bool
    {
        if (! $this->isNames($methodCall->name, ['dispatch', 'emit'])) {
            return true;
        }

        if ($this->isObjectType($methodCall->var, new ObjectType('League\Event\EventDispatcher'))) {
            return false;
        }

        if ($this->isObjectType($methodCall->var, new ObjectType('League\Event\Emitter'))) {
            return false;
        }

        if (! $this->getStaticType($methodCall->args[0]->value) instanceof StringType) {
            return true;
        }

        return true;
    }

    private function updateNode(MethodCall $methodCall): MethodCall
    {
        $methodCall->args[0] = new Arg($this->createNewAnonymousEventClass($methodCall->args[0]->value));
        return $methodCall;
    }

    private function createNewAnonymousEventClass(Expr $eventName): New_
    {
        $implements = [
            new FullyQualified('League\Event\HasEventName')
        ];

        return new New_(new Class_(null, [
            'implements' => $implements,
            'stmts' => $this->createAnonymousEventClassBody($eventName),
        ]));
    }

    /**
     * @param Expr $eventName
     * @return Stmt[]
     */
    private function createAnonymousEventClassBody(Expr $eventName): array
    {
        return [
            new ClassMethod('eventName', [
                'flags' => Class_::MODIFIER_PUBLIC,
                'returnType' => 'string',
                'stmts' => [
                    new Return_($eventName),
                ],
            ]),
        ];
    }
}
