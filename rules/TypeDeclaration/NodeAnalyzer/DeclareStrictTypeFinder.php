<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Declare_;
final class DeclareStrictTypeFinder
{
    public function hasDeclareStrictTypes(Stmt $stmt) : bool
    {
        // when first stmt is Declare_, verify if there is strict_types definition already,
        // as multiple declare is allowed, with declare(strict_types=1) only allowed on very first stmt
        if (!$stmt instanceof Declare_) {
            return \false;
        }
        foreach ($stmt->declares as $declare) {
            if ($declare->key->toString() === 'strict_types') {
                return \true;
            }
        }
        return \false;
    }
}
