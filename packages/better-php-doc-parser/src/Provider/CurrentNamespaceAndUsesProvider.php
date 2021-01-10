<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Provider;

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
        $node = $this->currentNodeProvider->getNode();
        if ($node === null) {
            return null;
        }

        return $node->getAttribute(AttributeKey::NAMESPACE_NAME);
    }

    /**
     * @return Use_[]
     */
    public function getUses(): array
    {
        $node = $this->currentNodeProvider->getNode();
        if ($node === null) {
            return [];
        }

        return (array) $node->getAttribute(AttributeKey::USE_NODES);
    }
}
