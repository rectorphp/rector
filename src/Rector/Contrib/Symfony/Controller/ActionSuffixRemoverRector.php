<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Controller;

use PhpParser\Node;
use Rector\Bridge\Symfony\NodeAnalyzer\ControllerMethodAnalyzer;
use Rector\Builder\IdentifierRenamer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

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

    public function isCandidate(Node $node): bool
    {
        return $this->controllerMethodAnalyzer->isAction($node);
    }

    public function refactor(Node $node): ?Node
    {
        $this->identifierRenamer->removeSuffix($node, 'Action');

        return $node;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes Action suffixes from methods in Symfony Controllers', [
            new CodeSample('public function indexAction(){...}', 'public function index(){...}'),
        ]);
    }
}
