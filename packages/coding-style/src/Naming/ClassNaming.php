<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Naming;

use Nette\Utils\Strings;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Resolver\NodeNameResolver;

final class ClassNaming
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @param string|Name|Identifier $name
     */
    public function getShortName($name): string
    {
        if ($name instanceof Name || $name instanceof Identifier) {
            $name = $this->nodeNameResolver->getName($name);
            if ($name === null) {
                throw new ShouldNotHappenException();
            }
        }

        $name = trim($name, '\\');

        return Strings::after($name, '\\', -1) ?: $name;
    }

    public function getNamespace(string $fullyQualifiedName): ?string
    {
        $fullyQualifiedName = trim($fullyQualifiedName, '\\');

        return Strings::before($fullyQualifiedName, '\\', -1) ?: null;
    }
}
