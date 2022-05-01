<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Naming;

use RectorPrefix20220501\Nette\Utils\Strings;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
final class ClassNaming
{
    /**
     * @param string|\PhpParser\Node\Name|\PhpParser\Node\Identifier $name
     */
    public function getVariableName($name) : string
    {
        $shortName = $this->getShortName($name);
        return \lcfirst($shortName);
    }
    /**
     * @param string|\PhpParser\Node\Name|\PhpParser\Node\Identifier|\PhpParser\Node\Stmt\ClassLike $name
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
        $shortName = \RectorPrefix20220501\Nette\Utils\Strings::after($name, '\\', -1);
        if (\is_string($shortName)) {
            return $shortName;
        }
        return $name;
    }
    public function getNamespace(string $fullyQualifiedName) : ?string
    {
        $fullyQualifiedName = \trim($fullyQualifiedName, '\\');
        return \RectorPrefix20220501\Nette\Utils\Strings::before($fullyQualifiedName, '\\', -1);
    }
}
