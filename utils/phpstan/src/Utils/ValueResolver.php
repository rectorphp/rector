<?php declare(strict_types=1);

namespace Rector\PHPStanExtensions\Utils;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

final class ValueResolver
{
    public function resolveClassConstFetch(ClassConstFetch $classConstFetch): ?string
    {
        $value = null;

        if ($classConstFetch->class instanceof Name) {
            $value = $classConstFetch->class->toString();
        } else {
            return null;
        }

        if ($classConstFetch->name instanceof Identifier) {
            $value .= '::' . $classConstFetch->name->toString();
        } else {
            return null;
        }

        return $value;
    }
}
