<?php declare(strict_types=1);

namespace Rector\Sylius\Rector\Review;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Builder\IdentifierRenamer;
use Rector\NodeAnalyzer\MethodArgumentAnalyzer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ReplaceCreateMethodWithoutReviewerRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var MethodArgumentAnalyzer
     */
    private $methodArgumentAnalyzer;

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        MethodArgumentAnalyzer $methodArgumentAnalyzer,
        IdentifierRenamer $identifierRenamer
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodArgumentAnalyzer = $methodArgumentAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns `createForSubjectWithReviewer()` with null review to standalone method in Sylius',
            [
                new CodeSample(
                    '$this->createForSubjectWithReviewer($subject, null)',
                    '$this->createForSubject($subject)'
                ),
            ]
        );
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        if (! $this->methodCallAnalyzer->isMethod($methodCallNode, 'createForSubjectWithReviewer')) {
            return null;
        }
        if ((! $this->methodArgumentAnalyzer->hasMethodNthArgument($methodCallNode, 2)
            || $this->methodArgumentAnalyzer->isMethodNthArgumentNull($methodCallNode, 2)) === false) {
            return null;
        }
        $this->identifierRenamer->renameNode($methodCallNode, 'createForSubject');

        if ($this->methodArgumentAnalyzer->hasMethodNthArgument($methodCallNode, 2)) {
            $methodCallNode->args = [array_shift($methodCallNode->args)];
        }

        return $methodCallNode;
    }
}
