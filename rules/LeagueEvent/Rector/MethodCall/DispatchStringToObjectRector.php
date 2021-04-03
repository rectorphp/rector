<?php

namespace Rector\LeagueEvent\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
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
        $methodName = $this->getName($node->name);
        if ($methodName !== 'dispatch') {
            return null;
        }

        if (! $this->isObjectType($node->var, new ObjectType('League\Event\EventDispatcher'))) {
            return null;
        }

        if (! $this->getStaticType($node->args[0]->value) instanceof StringType) {
            return null;
        }

        $implements = [
            new FullyQualified('League\Event\HasEventName')
        ];
        $statements = [
            new ClassMethod('eventName', [
                'flags' => Class_::MODIFIER_PUBLIC,
                'returnType' => 'string',
                'stmts' => [
                    new Return_($node->args[0]->value),
                ],
            ]),
        ];
        $node->args[0] = new Arg(new New_(new Class_(null, [
            'implements' => $implements,
            'stmts' => $statements,
        ])));

        return $node;
    }
}
