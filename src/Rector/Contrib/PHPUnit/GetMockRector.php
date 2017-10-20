<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

final class GetMockRector extends AbstractRector
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
        if (! $this->isInTestClass($node)) {
            return false;
        }

        return $this->methodCallAnalyzer->isMethod(
            $node,
            'getMock'
        );
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $methodCallNode->name->name = 'createMock';

        return $methodCallNode;
    }

    private function isInTestClass(Node $node): bool
    {
        $className = $node->getAttribute(Attribute::CLASS_NAME);

        return Strings::endsWith($className, 'Test');
    }
}
