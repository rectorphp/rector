<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\NodeFinder;

use RectorPrefix20211020\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class FullyQualifiedFromUseFinder
{
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function matchAliasNamespace(\PhpParser\Node\Stmt\Use_ $use, string $loweredAliasName) : ?\PhpParser\Node
    {
        return $this->betterNodeFinder->findFirstNext($use, function (\PhpParser\Node $node) use($loweredAliasName) : bool {
            if (!$node instanceof \PhpParser\Node\Name\FullyQualified) {
                return \false;
            }
            $originalName = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NAME);
            if (!$originalName instanceof \PhpParser\Node\Name) {
                return \false;
            }
            $loweredOriginalName = \strtolower($originalName->toString());
            $loweredOriginalNameNamespace = \RectorPrefix20211020\Nette\Utils\Strings::before($loweredOriginalName, '\\');
            return $loweredAliasName === $loweredOriginalNameNamespace;
        });
    }
}
