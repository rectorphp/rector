<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

// ref. https://github.com/RectorPHP/Rector/issues/79

final class ExceptionRector extends AbstractRector
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
        return $this->methodCallAnalyzer->isMethodCallMethod(
            $node,
            'setExpectedException'
        );
    }

    /**
     * @param MethodCall $node
     * @return null|Node
     */
    public function refactor(Node $node): ?Node
    {
        $node->name->name = 'expectException';

        return $node;
    }
}
