<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Controller;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Builder\IdentifierRenamer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Symfony\Bridge\NodeAnalyzer\ControllerMethodAnalyzer;

final class ActionSuffixRemoverRector extends AbstractRector
{
    /**
     * @var ControllerMethodAnalyzer
     */
    private $controllerMethodAnalyzer;

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    public function __construct(
        ControllerMethodAnalyzer $controllerMethodAnalyzer,
        IdentifierRenamer $identifierRenamer
    ) {
        $this->controllerMethodAnalyzer = $controllerMethodAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes Action suffixes from methods in Symfony Controllers', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeController
{
    public function indexAction()
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeController
{
    public function index()
    {
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
        return [ClassMethod::class];
    }

    public function refactor(Node $node): ?Node
    {
        if ($this->controllerMethodAnalyzer->isAction($node) === false) {
            return null;
        }
        $this->identifierRenamer->removeSuffix($node, 'Action');

        return $node;
    }
}
