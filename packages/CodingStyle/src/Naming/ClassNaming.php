<?php declare(strict_types=1);

namespace Rector\CodingStyle\Naming;

use Nette\Utils\Strings;
use PhpParser\Node\Name;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class ClassNaming
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(NameResolver $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    /**
     * @param string|Name $name
     * @return string
     */
    public function getShortName($name): string
    {
        if ($name instanceof Name) {
            $name = $this->nameResolver->getName($name);
            if ($name === null) {
                throw new ShouldNotHappenException(__METHOD__ . '() on line ' . __LINE__);
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
