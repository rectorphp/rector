<?php

declare(strict_types=1);

namespace Rector\Phalcon\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/rectorphp/rector/issues/2408
 *
 * @see \Rector\Phalcon\Tests\Rector\Assign\NewApplicationToToFactoryWithDefaultContainerRector\NewApplicationToToFactoryWithDefaultContainerRectorTest
 */
final class NewApplicationToToFactoryWithDefaultContainerRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change new application to default factory with application', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($di)
    {
        $application = new \Phalcon\Mvc\Application($di);

        $response = $application->handle();
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($di)
    {
        $container = new \Phalcon\Di\FactoryDefault();
        $application = new \Phalcon\Mvc\Application($container);

        $response = $application->handle($_SERVER["REQUEST_URI"]);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isNewApplication($node->expr)) {
            return null;
        }

        if (! $node->expr instanceof New_) {
            return null;
        }

        $containerVariable = new Variable('container');
        $factoryAssign = $this->createNewContainerToFactoryDefaultAssign($containerVariable);

        $node->expr->args = [new Arg($containerVariable)];

        $this->addNodeBeforeNode($factoryAssign, $node);

        return $node;
    }

    private function isNewApplication(Expr $expr): bool
    {
        if (! $expr instanceof New_) {
            return false;
        }
        return $this->isName($expr->class, 'Phalcon\Mvc\Application');
    }

    private function createNewContainerToFactoryDefaultAssign(Variable $variable): Assign
    {
        return new Assign($variable, new New_(new FullyQualified('Phalcon\Di\FactoryDefault')));
    }
}
