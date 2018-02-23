<?php declare(strict_types=1);

namespace Rector\RectorBuilder;

use PhpParser\Node;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

final class CaseRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        dump('EE');
        die;
        // TODO: Implement isCandidate() method.
    }

    public function refactor(Node $node): ?Node
    {
        // TODO: Implement refactor() method.
    }
}
