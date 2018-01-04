<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Namespace_;
use Rector\BetterReflection\Reflector\SmartClassReflector;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class NameTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var SmartClassReflector
     */
    private $smartClassReflector;

    public function __construct(SmartClassReflector $smartClassReflector)
    {
        $this->smartClassReflector = $smartClassReflector;
    }

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Name::class, FullyQualified::class];
    }

    /**
     * @param Name $nameNode
     * @return string[]
     */
    public function resolve(Node $nameNode): array
    {
        $stringName = $nameNode->toString();

        if ($this->shouldSkip($nameNode, $stringName)) {
            return [];
        }

        if ($nameNode->toString() === 'self') {
            $fullyQualifiedName = $nameNode->getAttribute(Attribute::CLASS_NAME);
        } else {
            $fullyQualifiedName = $this->resolveFullyQualifiedName($nameNode, $stringName);
        }

        // known types, for performance
        if ($fullyQualifiedName === 'PHPUnit\Framework\TestCase') {
            return ['PHPUnit\Framework\TestCase'];
        }

        if ($fullyQualifiedName === 'parent') {
            return [$nameNode->getAttribute(Attribute::PARENT_CLASS_NAME)];
        }

        // skip tests, since they are autoloaded and decoupled from the prod code
        // reflection is too performance heavy
        if (Strings::contains($fullyQualifiedName, 'Tests') && Strings::endsWith($fullyQualifiedName, 'Test')) {
            return [$fullyQualifiedName];
        }

        return $this->reflectClassLike($fullyQualifiedName);
    }

    private function shouldSkip(Node $nameNode, string $stringName): bool
    {
        // is simple name?
        if (in_array($stringName, ['true', 'false', 'stdClass'], true)) {
            return true;
        }

        // is parent namespace?
        if ($nameNode->getAttribute(Attribute::PARENT_NODE) instanceof Namespace_) {
            return true;
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function reflectClassLike(string $name): array
    {
        $classLikeReflection = $this->smartClassReflector->reflect($name);
        if ($classLikeReflection === null) {
            return [$name];
        }

        return array_merge(
            [$name],
            $this->smartClassReflector->resolveClassInterfaces($classLikeReflection),
            $this->smartClassReflector->resolveClassParents($classLikeReflection)
        );
    }

    private function resolveFullyQualifiedName(Node $nameNode, string $stringName): string
    {
        /** @var Name|null $name */
        $name = $nameNode->getAttribute(Attribute::RESOLVED_NAME);
        if ($name instanceof Name) {
            return $name->toString();
        }

        return $stringName;
    }
}
