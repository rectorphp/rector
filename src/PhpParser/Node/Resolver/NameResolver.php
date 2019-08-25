<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Resolver;

use Nette\Utils\Strings;
use PhpParser\Builder\Trait_;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use Rector\Collector\CallableCollectorPopulator;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class NameResolver
{
    /**
     * @var callable[]
     */
    private $nameResolversPerNode = [];

    public function __construct(CallableCollectorPopulator $callableCollectorPopulator)
    {
        $resolvers = [
            Empty_::class => 'empty',
            // more complex
            function (ClassConst $classConstNode): ?string {
                if (count($classConstNode->consts) === 0) {
                    return null;
                }

                return $this->getName($classConstNode->consts[0]);
            },
            function (Property $propertyNode): ?string {
                if (count($propertyNode->props) === 0) {
                    return null;
                }

                return $this->getName($propertyNode->props[0]);
            },
            function (Use_ $useNode): ?string {
                if (count($useNode->uses) === 0) {
                    return null;
                }

                return $this->getName($useNode->uses[0]);
            },
            function (Param $paramNode): ?string {
                return $this->getName($paramNode->var);
            },
            function (Name $nameNode): string {
                $resolvedName = $nameNode->getAttribute(AttributeKey::RESOLVED_NAME);
                if ($resolvedName instanceof FullyQualified) {
                    return $resolvedName->toString();
                }

                return $nameNode->toString();
            },
            function (Class_ $classNode): ?string {
                if (isset($classNode->namespacedName)) {
                    return $classNode->namespacedName->toString();
                }
                if ($classNode->name === null) {
                    return null;
                }

                return $this->getName($classNode->name);
            },
            function (Interface_ $interface): ?string {
                return $this->resolveNamespacedNameAwareNode($interface);
            },
            function (Node\Stmt\Trait_ $trait): ?string {
                return $this->resolveNamespacedNameAwareNode($trait);
            },
            function (ClassConstFetch $classConstFetch): ?string {
                $class = $this->getName($classConstFetch->class);
                $name = $this->getName($classConstFetch->name);

                if ($class === null || $name === null) {
                    return null;
                }

                return $class . '::' . $name;
            },
        ];

        $this->nameResolversPerNode = $callableCollectorPopulator->populate($resolvers);
    }

    /**
     * @param string[] $map
     */
    public function matchNameInMap(Node $node, array $map): ?string
    {
        foreach ($map as $nameToMatch => $return) {
            if ($this->isName($node, $nameToMatch)) {
                return $return;
            }
        }

        return null;
    }

    /**
     * @param string[] $names
     */
    public function isNames(Node $node, array $names): bool
    {
        foreach ($names as $name) {
            if ($this->isName($node, $name)) {
                return true;
            }
        }

        return false;
    }

    public function isName(Node $node, string $name): bool
    {
        $resolvedName = $this->getName($node);
        if ($resolvedName === null) {
            return false;
        }

        if ($name === '') {
            return false;
        }

        // is probably regex pattern
        if (($name[0] === $name[strlen($name) - 1]) && ! ctype_alpha($name[0])) {
            return (bool) Strings::match($resolvedName, $name);
        }

        // is probably fnmatch
        if (Strings::contains($name, '*')) {
            return fnmatch($name, $resolvedName, FNM_NOESCAPE);
        }

        // special case
        if ($name === 'Object') {
            return $name === $resolvedName;
        }

        return strtolower($resolvedName) === strtolower($name);
    }

    public function getName(Node $node): ?string
    {
        foreach ($this->nameResolversPerNode as $type => $nameResolver) {
            if (is_a($node, $type, true)) {
                return $nameResolver($node);
            }
        }

        if (! property_exists($node, 'name')) {
            return null;
        }

        // unable to resolve
        if ($node->name instanceof Expr) {
            return null;
        }

        if ($node instanceof Variable) {
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            // is $variable::method(), unable to resolve $variable->class name
            if ($parentNode instanceof StaticCall) {
                return null;
            }
        }

        return (string) $node->name;
    }

    public function areNamesEqual(Node $firstNode, Node $secondNode): bool
    {
        return $this->getName($firstNode) === $this->getName($secondNode);
    }

    /**
     * @param Interface_|Trait_ $classLike
     */
    private function resolveNamespacedNameAwareNode(ClassLike $classLike): ?string
    {
        if (isset($classLike->namespacedName)) {
            return $classLike->namespacedName->toString();
        }

        if ($classLike->name === null) {
            return null;
        }

        return $this->getName($classLike->name);
    }
}
