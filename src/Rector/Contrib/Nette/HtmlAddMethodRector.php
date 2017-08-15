<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\Deprecation\SetNames;
use Rector\NodeTraverser\TokenSwitcher;
use Rector\Rector\AbstractRector;

final class HtmlAddMethodRector extends AbstractRector
{
    /**
     * @var TokenSwitcher
     */
    private $tokenSwitcher;

    public function __construct(TokenSwitcher $tokenSwitcher)
    {
        $this->tokenSwitcher = $tokenSwitcher;
    }

    public function getSetName(): string
    {
        return SetNames::NETTE;
    }

    public function sinceVersion(): float
    {
        return 2.4;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        if (! $node->name instanceof Identifier) {
            return false;
        }

        if ($node->class->getLast() !== 'Html') {
            return false;
        }

        if ((string) $node->name !== 'add') {
            return false;
        }

        return true;
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $node->name->name = 'addHtml';

        return $node;
    }

    /**
     * @param Node[] $nodes
     */
    public function afterTraverse(array $nodes): void
    {
        $this->tokenSwitcher->enable();
    }
}
