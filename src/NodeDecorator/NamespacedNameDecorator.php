<?php

declare (strict_types=1);
namespace Rector\Core\NodeDecorator;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
final class NamespacedNameDecorator
{
    /**
     * @param \PhpParser\Node|mixed[] $node
     */
    public function decorate($node) : void
    {
        $nodes = \is_array($node) ? $node : [$node];
        // traverse with node name resolver, to to comply with PHPStan default parser
        $nameResolver = new NameResolver(null, ['replaceNodes' => \false, 'preserveOriginalNames' => \true]);
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($nameResolver);
        $nodeTraverser->traverse($nodes);
    }
}
