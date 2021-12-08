<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Naming;

use Nette\Utils\Strings;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;

final class ClassNaming
{
    public function getVariableName(string | Name | Identifier $name): string
    {
        $shortName = $this->getShortName($name);
        return lcfirst($shortName);
    }

    public function getShortName(string | Name | Identifier | ClassLike $name): string
    {
        if ($name instanceof ClassLike) {
            if ($name->name === null) {
                return '';
            }

            return $this->getShortName($name->name);
        }

        if ($name instanceof Name || $name instanceof Identifier) {
            $name = $name->toString();
        }

        $name = trim($name, '\\');

        $shortName = Strings::after($name, '\\', -1);
        if (is_string($shortName)) {
            return $shortName;
        }

        return $name;
    }

    public function getNamespace(string $fullyQualifiedName): ?string
    {
        $fullyQualifiedName = trim($fullyQualifiedName, '\\');

        return Strings::before($fullyQualifiedName, '\\', -1);
    }
}
