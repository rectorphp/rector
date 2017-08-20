<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;

final class NamingNodeTraverser extends NodeTraverser
{
    /**
     * NameResolver adds $namespacedName.
     * @see https://github.com/nikic/PHP-Parser/blob/7b36ca3b6cc1b99210c6699074d6091061e73eea/lib/PhpParser/Node/Stmt/ClassLike.php#L8
     */
    public function __construct(NameResolver $nameResolver)
    {
        $this->addVisitor($nameResolver);
    }
}
