<?php declare(strict_types=1);


namespace Rector\Rector\Dynamic;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
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

    public function __construct(array $pseudoNamespacePrefixes)
    {
        $this->pseudoNamespacePrefixes = $pseudoNamespacePrefixes;
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
     * @return null|Node
     */
    public function refactor(Node $nameNode): ?Node
    {
        $newNameParts = explode('_', $nameNode->toString());

        $nameNode->parts = $newNameParts;

        return $nameNode;
    }
}
