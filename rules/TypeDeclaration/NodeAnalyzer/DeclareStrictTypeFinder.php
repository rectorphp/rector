<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Declare_;
final class DeclareStrictTypeFinder
{
    public function hasDeclareStrictTypes(Node $node): bool
    {
        // when first node is Declare_, verify if there is strict_types definition already,
        // as multiple declare is allowed, with declare(strict_types=1) only allowed on very first node
        if (!$node instanceof Declare_) {
            return \false;
        }
        foreach ($node->declares as $declare) {
            if ($declare->key->toString() === 'strict_types') {
                return \true;
            }
        }
        return \false;
    }
}
