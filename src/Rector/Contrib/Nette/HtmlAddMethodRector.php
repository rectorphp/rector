<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use Nette\Utils\Html;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\Deprecation\SetNames;
use Rector\Rector\AbstractRector;

final class HtmlAddMethodRector extends AbstractRector
{
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
        if (! $this->isOnTypeCall($node, Html::class)) {
            return false;
        }

        if (! $this->isStaticCall($node)) {
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
        dump($class);
        die;

        # check elements type:
        # inspire: https://github.com/phpstan/phpstan/blob/355060961eb4a33304c66dfbfc0cd32870a0b9d4/src/Rules/Methods/CallMethodsRule.php#L74
        # local package?
    }
}
