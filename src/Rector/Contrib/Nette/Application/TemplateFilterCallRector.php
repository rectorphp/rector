<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Application;

use PhpParser\Node;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * $this->template->someFilter(...);
 *
 * After:
 * $this->template->getLatte()->invokeFilter('someFilter', ...)
 */
final class TemplateFilterCallRector extends AbstractRector
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
        if (! $this->methodCallAnalyzer->isMethodCallType($node, 'Nette\Bridges\ApplicationLatte\Template')) {
            return false;
        }

        dump($node);
        die;
    }

    public function refactor(Node $propertyNode): Node
    {
    }
}
