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
        if ($nameNode->toString() === 'parent') {
            return [$nameNode->getAttribute(Attribute::PARENT_CLASS_NAME)];
        }

        if ($this->shouldSkip($nameNode, $nameNode->toString())) {
            return [];
        }

        $fullyQualifiedName = $this->resolveFullyQualifiedName($nameNode, $nameNode->toString());

        // known types, for performance
        if ($fullyQualifiedName === 'PHPUnit\Framework\TestCase') {
            return ['PHPUnit\Framework\TestCase'];
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
        return $nameNode->getAttribute(Attribute::PARENT_NODE) instanceof Namespace_;
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

//        if (class_exists($classLikeReflection->getName())) {
        $useTraits = array_values((array) class_uses($classLikeReflection->getName()));
//        } else {
//            $useTraits = [];
//        }

        $implementedInterfaces = $this->smartClassReflector->resolveClassInterfaces($classLikeReflection);
        $classParents = $this->smartClassReflector->resolveClassParents($classLikeReflection);

        return array_merge([$name], $classParents, $useTraits, $implementedInterfaces);
    }

    private function resolveFullyQualifiedName(Node $nameNode, string $name): string
    {
        if (in_array($name, ['self', 'static', 'this'], true)) {
            return $nameNode->getAttribute(Attribute::CLASS_NAME);
        }

        /** @var Name|null $name */
        $resolvedNameNode = $nameNode->getAttribute(Attribute::RESOLVED_NAME);
        if ($resolvedNameNode instanceof Name) {
            return $resolvedNameNode->toString();
        }

        return $name;
    }
}
