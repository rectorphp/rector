<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Naming;

use RectorPrefix20211231\Nette\Utils\Strings;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
final class ClassNaming
{
    /**
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Name|string $name
     */
    public function getVariableName($name) : string
    {
        $shortName = $this->getShortName($name);
        return \lcfirst($shortName);
    }
    /**
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\Stmt\ClassLike|string $name
     */
    public function getShortName($name) : string
    {
        if ($name instanceof \PhpParser\Node\Stmt\ClassLike) {
            if ($name->name === null) {
                return '';
            }
            return $this->getShortName($name->name);
        }
        if ($name instanceof \PhpParser\Node\Name || $name instanceof \PhpParser\Node\Identifier) {
            $name = $name->toString();
        }
        $name = \trim($name, '\\');
        $shortName = \RectorPrefix20211231\Nette\Utils\Strings::after($name, '\\', -1);
        if (\is_string($shortName)) {
            return $shortName;
        }
        return $name;
    }
    public function getNamespace(string $fullyQualifiedName) : ?string
    {
        $fullyQualifiedName = \trim($fullyQualifiedName, '\\');
        return \RectorPrefix20211231\Nette\Utils\Strings::before($fullyQualifiedName, '\\', -1);
    }
}
