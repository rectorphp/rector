<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Symfony\Rector\MethodCall\AbstractToConstructorInjectionRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector\ContextGetByTypeToConstructorInjectionRectorTest
 */
final class ContextGetByTypeToConstructorInjectionRector extends AbstractToConstructorInjectionRector
{
    /**
     * @var TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;

    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Move dependency get via $context->getByType() to constructor injection',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    public function run()
    {
        $someTypeToInject = $this->context->getByType(SomeTypeToInject::class);
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var \Nette\DI\Container
     */
    private $context;

    public function __construct(private SomeTypeToInject $someTypeToInject)
    {
    }

    public function run()
    {
        $someTypeToInject = $this->someTypeToInject;
    }
}
CODE_SAMPLE
            ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }

        if (! $node->var instanceof PropertyFetch) {
            return null;
        }

        if (! $this->isObjectType($node->var, 'Nette\DI\Container')) {
            return null;
        }

        if (! $this->isName($node->name, 'getByType')) {
            return null;
        }

        return $this->processMethodCallNode($node);
    }
}
