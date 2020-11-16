<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\RectorGenerator\Contract\InternalRectorInterface;
use Rector\SymfonyPhpConfig\NodeAnalyzer\SymfonyPhpConfigClosureAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class AddNewServiceToSymfonyPhpConfigRector extends AbstractRector implements InternalRectorInterface
{
    /**
     * @var SymfonyPhpConfigClosureAnalyzer
     */
    private $symfonyPhpConfigClosureAnalyzer;

    /**
     * @var string|null
     */
    private $rectorClass;

    public function __construct(SymfonyPhpConfigClosureAnalyzer $symfonyPhpConfigClosureAnalyzer)
    {
        $this->symfonyPhpConfigClosureAnalyzer = $symfonyPhpConfigClosureAnalyzer;
    }

    public function setRectorClass(string $rectorClass): void
    {
        $this->rectorClass = $rectorClass;
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
        if ($this->rectorClass === null) {
            return null;
        }

        if (! $this->symfonyPhpConfigClosureAnalyzer->isPhpConfigClosure($node)) {
            return null;
        }

        $methodCall = $this->createServicesSetMethodCall($this->rectorClass);
        $node->stmts[] = new Expression($methodCall);

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Adds a new $services->set(...) call to PHP Config', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
};
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AddNewServiceToSymfonyPhpConfigRector::class);
};
CODE_SAMPLE
            ),
        ]);
    }

    private function createServicesSetMethodCall(string $className): MethodCall
    {
        $servicesVariable = new Variable('services');
        $referenceClassConstFetch = new ClassConstFetch(new FullyQualified($className), new Identifier('class'));
        $args = [new Arg($referenceClassConstFetch)];

        return new MethodCall($servicesVariable, 'set', $args);
    }
}
