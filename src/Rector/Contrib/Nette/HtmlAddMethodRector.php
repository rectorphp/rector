<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use Nette\Utils\Html;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use Rector\Deprecation\SetNames;
use Rector\Rector\AbstractRector;

final class HtmlAddMethodRector extends AbstractRector
{
    /**
     * @var Node[]
     */
    private $fileNodes = [];

    public function getSetName(): string
    {
        return SetNames::NETTE;
    }

    public function sinceVersion(): float
    {
        return 2.4;
    }

    public function beforeTraverse(array $nodes): void
    {
        $this->fileNodes = $nodes;
    }

    public function isCandidate(Node $node): bool
    {
        if ($this->isOnTypeCall($node, Html::class)) {
            return true;
        }

        if ($this->isStaticCall($node)) {
            return true;
        }

        return false;
    }

    /**
     * @param StaticCall|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $node->name->name = 'addHtml';

        return $node;
    }

    private function isStaticCall(Node $node): bool
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

    private function isOnTypeCall(Node $node, string $class): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->var instanceof Variable) {
            return false;
        }

        dump($node->var);
        dump($node->getAttribute('type'));
        dump($node->getAttribute('type') === $class);
        die;

        return false;
    }
}
