<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Stmt\Declare_;
use Rector\PhpParser\Node\FileNode;
final class DeclareStrictTypeFinder
{
    public function hasDeclareStrictTypes(FileNode $fileNode): bool
    {
        // when first fileNode is Declare_, verify if there is strict_types definition already,
        // as multiple declare is allowed, with declare(strict_types=1) only allowed on very first fileNode
        foreach ($fileNode->stmts as $stmt) {
            if (!$stmt instanceof Declare_) {
                continue;
            }
            foreach ($stmt->declares as $declare) {
                if ($declare->key->toString() === 'strict_types') {
                    return \true;
                }
            }
        }
        return \false;
    }
}
