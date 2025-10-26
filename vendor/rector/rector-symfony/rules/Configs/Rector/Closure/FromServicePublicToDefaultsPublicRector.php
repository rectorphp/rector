<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Configs\NodeDecorator\ServiceDefaultsCallClosureDecorator;
use Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Configs\Rector\Closure\FromServicePublicToDefaultsPublicRector\FromServicePublicToDefaultsPublicRectorTest
 */
final class FromServicePublicToDefaultsPublicRector extends AbstractRector
{
    /**
     * @readonly
     */
    private SymfonyPhpClosureDetector $symfonyPhpClosureDetector;
    /**
     * @readonly
     */
    private ServiceDefaultsCallClosureDecorator $serviceDefaultsCallClosureDecorator;
    public function __construct(SymfonyPhpClosureDetector $symfonyPhpClosureDetector, ServiceDefaultsCallClosureDecorator $serviceDefaultsCallClosureDecorator)
    {
        $this->symfonyPhpClosureDetector = $symfonyPhpClosureDetector;
        $this->serviceDefaultsCallClosureDecorator = $serviceDefaultsCallClosureDecorator;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Instead of per service public() call, use it once in $services->defaults()->public()', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeCommand::class)
        ->public();

    $services->set(AnotherCommand::class)
        ->public();

    $services->set(NextCommand::class)
        ->public();
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()->public();

    $services->set(SomeCommand::class);
    $services->set(AnotherCommand::class);
    $services->set(NextCommand::class);
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->symfonyPhpClosureDetector->detect($node)) {
            return null;
        }
        $hasDefaultsPublic = $this->symfonyPhpClosureDetector->hasDefaultsConfigured($node, 'public');
        $hasChanged = \false;
        $this->traverseNodesWithCallable($node->stmts, function (Node $node) use (&$hasChanged): ?Expr {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isName($node->name, 'public')) {
                return null;
            }
            // skip ->defaults()->public()
            if ($this->isDefaultsCall($node)) {
                return null;
            }
            $hasChanged = \true;
            return $node->var;
        });
        if ($hasChanged === \false) {
            return null;
        }
        if ($hasDefaultsPublic === \false) {
            $this->serviceDefaultsCallClosureDecorator->decorate($node, 'public');
        }
        return $node;
    }
    public function isDefaultsCall(MethodCall $methodCall): bool
    {
        $currentMethodCall = $methodCall;
        while ($currentMethodCall instanceof MethodCall) {
            if ($this->isName($currentMethodCall->name, 'defaults')) {
                return \true;
            }
            $currentMethodCall = $currentMethodCall->var;
        }
        return \false;
    }
}
