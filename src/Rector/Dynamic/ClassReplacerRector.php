<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\UseStatements;
use Rector\Rector\AbstractRector;

final class ClassReplacerRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $oldToNewClasses = [];

    /**
     * @param string[] $oldToNewClasses
     */
    public function __construct(array $oldToNewClasses)
    {
        $this->oldToNewClasses = $oldToNewClasses;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Name && ! $node instanceof Use_) {
            return false;
        }

        $name = $this->resolveNameFromNode($node);

        return isset($this->oldToNewClasses[$name]);
    }

    /**
     * @param Name|UseUse $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Name) {
            $newName = $this->getNewName($node->toString());

            return new FullyQualified($newName);
        }

        if ($node instanceof Use_) {
            $name = $this->resolveNameFromNode($node);
            $newName = $this->getNewName($name);

            if ($this->isUseStatmenetAlreadyPresent($node, $newName)) {
                $this->shouldRemoveNode = true;
            }
        }

        return null;
    }

    private function getNewName(string $oldName): string
    {
        return $this->oldToNewClasses[$oldName];
    }

    private function isUseStatmenetAlreadyPresent(Use_ $useNode, string $newName): bool
    {
        /** @var UseStatements $useStatments */
        $useStatments = $useNode->getAttribute(Attribute::USE_STATEMENTS);

        return in_array($newName, $useStatments->getUseStatements(), true);
    }

    private function resolveNameFromNode(Node $node): string
    {
        if ($node instanceof Name) {
            return $node->toString();
        }

        if ($node instanceof Use_) {
            return $node->uses[0]->name->toString();
        }

        return '';
    }
}
