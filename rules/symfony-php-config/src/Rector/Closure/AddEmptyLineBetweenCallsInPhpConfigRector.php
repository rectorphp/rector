<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Nop;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\SymfonyPhpConfig\NodeAnalyzer\SymfonyPhpConfigClosureAnalyzer;

/**
 * @see \Rector\SymfonyPhpConfig\Tests\Rector\Closure\AddEmptyLineBetweenCallsInPhpConfigRector\AddEmptyLineBetweenCallsInPhpConfigRectorTest
 */
final class AddEmptyLineBetweenCallsInPhpConfigRector extends AbstractRector
{
    /**
     * @var SymfonyPhpConfigClosureAnalyzer
     */
    private $symfonyPhpConfigClosureAnalyzer;

    public function __construct(SymfonyPhpConfigClosureAnalyzer $symfonyPhpConfigClosureAnalyzer)
    {
        $this->symfonyPhpConfigClosureAnalyzer = $symfonyPhpConfigClosureAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Make calls in PHP Symfony config separated by newline', [
            new CodeSample(
                <<<'PHP'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set('key', 'value');
};
PHP
,
                <<<'PHP'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('key', 'value');
};
PHP
            ),
        ]);
    }

    /**
     * @return string[]
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
        if (! $this->symfonyPhpConfigClosureAnalyzer->isPhpConfigClosure($node)) {
            return null;
        }

        $originalStmts = $node->stmts;

        $newStmts = [];
        $previousLine = null;

        foreach ($originalStmts as $stmt) {
            if ($previousLine !== null && $previousLine + 1 === $stmt->getEndLine()) {
                $newStmts[] = new Nop();
            }

            $newStmts[] = $stmt;
            $previousLine = $stmt->getEndLine();
        }

        $node->stmts = $newStmts;

        return $node;
    }
}
