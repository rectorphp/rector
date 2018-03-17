<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Sylius\Review;

use PhpParser\Node;
use Rector\NodeAnalyzer\MethodArgumentAnalyzer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\IdentifierRenamer;
use Rector\Rector\AbstractRector;

/**
 * @author Eduard Barnáš <barnas@neoweb.sk>
 */
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

    /**
     * @var string
     */
    private $oldMethodName = 'createForSubjectWithReviewer';

    /**
     * @var string
     */
    private $newMethodName = 'createForSubject';

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        MethodArgumentAnalyzer $methodArgumentAnalyzer,
        IdentifierRenamer $identifierRenamer
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodArgumentAnalyzer = $methodArgumentAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isMethod($node, $this->oldMethodName)) {
            return false;
        }

        return (! $this->methodArgumentAnalyzer->hasMethodSecondArgument($node) || $this->methodArgumentAnalyzer->isMethodSecondArgumentNull($node));
    }

    public function refactor(Node $node): ?Node
    {
        $this->identifierRenamer->renameNode($node, $this->newMethodName);

        if ($this->methodArgumentAnalyzer->hasMethodSecondArgument($node)) {
            $node->args = [
                array_shift($node->args)
            ];
        }

        return $node;
    }
}