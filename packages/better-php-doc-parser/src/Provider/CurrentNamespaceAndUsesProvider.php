<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Provider;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpdocParserPrinter\Contract\Provider\CurrentNamespaceAndUsesProviderInterface;

final class CurrentNamespaceAndUsesProvider implements CurrentNamespaceAndUsesProviderInterface
{
    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    public function __construct(CurrentNodeProvider $currentNodeProvider)
    {
        $this->currentNodeProvider = $currentNodeProvider;
    }

    public function getNamespace(): ?string
    {
        $currentNode = $this->currentNodeProvider->getNode();
        if (! $currentNode instanceof Node) {
            return null;
        }

        return $currentNode->getAttribute(AttributeKey::NAMESPACE_NAME);
    }

    /**
     * @return Use_[]
     */
    public function getUses(): array
    {
        $currentNode = $this->currentNodeProvider->getNode();
        if (! $currentNode instanceof Node) {
            return [];
        }

        return $currentNode->getAttribute(AttributeKey::USE_NODES);
    }
}
