<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\NodeFinder;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class FullyQualifiedFromUseFinder
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder
    ) {
    }

    public function matchAliasNamespace(Use_ $use, string $loweredAliasName): ?Node
    {
        return $this->betterNodeFinder->findFirstNext($use, function (Node $node) use ($loweredAliasName): bool {
            if (! $node instanceof FullyQualified) {
                return false;
            }

            $originalName = $node->getAttribute(AttributeKey::ORIGINAL_NAME);
            if (! $originalName instanceof Name) {
                return false;
            }

            $loweredOriginalName = strtolower($originalName->toString());
            $loweredOriginalNameNamespace = Strings::before($loweredOriginalName, '\\');
            return $loweredAliasName === $loweredOriginalNameNamespace;
        });
    }
}
