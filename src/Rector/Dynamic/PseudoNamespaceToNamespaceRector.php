<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\UseUse;
use Rector\Node\Attribute;
use Rector\Rector\AbstractRector;

/**
 * Basically inversion of https://github.com/nikic/PHP-Parser/blob/master/doc/2_Usage_of_basic_components.markdown#example-converting-namespaced-code-to-pseudo-namespaces
 *
 *
 * Requested on SO: https://stackoverflow.com/questions/29014957/converting-pseudo-namespaced-classes-to-use-real-namespace
 *
 * @todo might handle current @see phpunit60.yml @see \Rector\Rector\Dynamic\NamespaceReplacerRector
 */
final class PseudoNamespaceToNamespaceRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $pseudoNamespacePrefixes = [];

    /**
     * @var string[]
     */
    private $oldToNewUseStatements = [];

    /**
     * @param string[] $pseudoNamespacePrefixes
     */
    public function __construct(array $pseudoNamespacePrefixes)
    {
        $this->pseudoNamespacePrefixes = $pseudoNamespacePrefixes;
    }

    /**
     * @param mixed[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        $this->oldToNewUseStatements = [];
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Name) {
            return false;
        }

        foreach ($this->pseudoNamespacePrefixes as $pseudoNamespacePrefix) {
            if (Strings::startsWith($node->toString(), $pseudoNamespacePrefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Name $nameNode
     */
    public function refactor(Node $nameNode): ?Node
    {
        $oldName = $nameNode->toString();
        $newNameParts = explode('_', $oldName);

        $parentNode = $nameNode->getAttribute(Attribute::PARENT_NODE);

        if ($parentNode instanceof UseUse) {
            $lastNewNamePart = $newNameParts[count($newNameParts) - 1];
            $this->oldToNewUseStatements[$oldName] = $lastNewNamePart;
        } elseif (isset($this->oldToNewUseStatements[$oldName])) {
            // to prevent "getComments() on string" error
            $nameNode->setAttribute('origNode', null);
            $newNameParts = [$this->oldToNewUseStatements[$oldName]];
        }

        $nameNode->parts = $newNameParts;

        return $nameNode;
    }
}
