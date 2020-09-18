<?php

declare(strict_types=1);

namespace Rector\Utils\NodeDocumentationGenerator\Category;

use Nette\Utils\Strings;

final class CategoryResolver
{
    public function resolveCategoryByNodeClass(string $nodeClass): string
    {
        if (Strings::contains($nodeClass, '\\Scalar\\')) {
            return 'Scalar nodes';
        }

        if (Strings::contains($nodeClass, '\\Expr\\')) {
            return 'Expressions';
        }

        if (Strings::contains($nodeClass, '\\Stmt\\')) {
            return 'Statements';
        }

        return 'Various';
    }
}
