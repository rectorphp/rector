<?php declare(strict_types=1);

namespace Rector\Sylius\Rector\Review;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Builder\IdentifierRenamer;
use Rector\NodeAnalyzer\MethodArgumentAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ReplaceCreateMethodWithoutReviewerRector extends AbstractRector
{
    /**
     * @var MethodArgumentAnalyzer
     */
    private $methodArgumentAnalyzer;

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    public function __construct(
        MethodArgumentAnalyzer $methodArgumentAnalyzer,
        IdentifierRenamer $identifierRenamer
    ) {
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
        if (! $this->isName($node, 'createForSubjectWithReviewer')) {
            return null;
        }

        if ((! $this->methodArgumentAnalyzer->hasMethodNthArgument($node, 2)
            || $this->methodArgumentAnalyzer->isMethodNthArgumentNull($node, 2)) === false) {
            return null;
        }

        $this->identifierRenamer->renameNode($node, 'createForSubject');

        if ($this->methodArgumentAnalyzer->hasMethodNthArgument($node, 2)) {
            $node->args = [array_shift($node->args)];
        }

        return $node;
    }
}
