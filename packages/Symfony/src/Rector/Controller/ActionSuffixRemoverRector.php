<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\Controller;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\PhpParser\Node\Manipulator\IdentifierManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Symfony\Bridge\NodeAnalyzer\ControllerMethodAnalyzer;

/**
 * @see \Rector\Symfony\Tests\Rector\Controller\ActionSuffixRemoverRector\ActionSuffixRemoverRectorTest
 */
final class ActionSuffixRemoverRector extends AbstractRector
{
    /**
     * @var ControllerMethodAnalyzer
     */
    private $controllerMethodAnalyzer;

    /**
     * @var IdentifierManipulator
     */
    private $identifierManipulator;

    public function __construct(
        ControllerMethodAnalyzer $controllerMethodAnalyzer,
        IdentifierManipulator $identifierManipulator
    ) {
        $this->controllerMethodAnalyzer = $controllerMethodAnalyzer;
        $this->identifierManipulator = $identifierManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes Action suffixes from methods in Symfony Controllers', [
            new CodeSample(
                <<<'PHP'
class SomeController
{
    public function indexAction()
    {
    }
}
PHP
                ,
                <<<'PHP'
class SomeController
{
    public function index()
    {
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (! $this->controllerMethodAnalyzer->isAction($node)) {
            return null;
        }

        $this->identifierManipulator->removeSuffix($node, 'Action');

        return $node;
    }
}
