<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Resolver;

use Nette\Utils\Strings;
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
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use Rector\Collector\CallableCollectorPopulator;
use Rector\NodeTypeResolver\Node\Attribute;

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

                return $this->resolve($classConstNode->consts[0]);
            },
            function (Property $propertyNode): ?string {
                if (count($propertyNode->props) === 0) {
                    return null;
                }

                return $this->resolve($propertyNode->props[0]);
            },
            function (Use_ $useNode): ?string {
                if (count($useNode->uses) === 0) {
                    return null;
                }

                return $this->resolve($useNode->uses[0]);
            },
            function (Param $paramNode): ?string {
                return $this->resolve($paramNode->var);
            },
            function (Name $nameNode): string {
                $resolvedName = $nameNode->getAttribute(Attribute::RESOLVED_NAME);
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

                return $this->resolve($classNode->name);
            },
            function (Interface_ $interfaceNode): ?string {
                if (isset($interfaceNode->namespacedName)) {
                    return $interfaceNode->namespacedName->toString();
                }

                if ($interfaceNode->name === null) {
                    return null;
                }

                return $this->resolve($interfaceNode->name);
            },
            function (ClassConstFetch $classConstFetch): ?string {
                $class = $this->resolve($classConstFetch->class);
                $name = $this->resolve($classConstFetch->name);

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
    public function matchNameInsensitiveInMap(Node $node, array $map): ?string
    {
        foreach ($map as $nameToMatch => $return) {
            if ($this->isNameInsensitive($node, $nameToMatch)) {
                return $return;
            }
        }

        return null;
    }

    public function isNameInsensitive(Node $node, string $name): bool
    {
        return strtolower((string) $this->resolve($node)) === strtolower($name);
    }

    public function isName(Node $node, string $name): bool
    {
        $resolvedName = $this->resolve($node);

        if (! isset($name[0])) {
            return false;
        }

        // is probably regex pattern
        if (($name[0] === $name[strlen($name) - 1]) && ! ctype_alpha($name[0])) {
            return (bool) Strings::match($resolvedName, $name);
        }

        return $resolvedName === $name;
    }

    /**
     * @param string[] $names
     */
    public function isNames(Node $node, array $names): bool
    {
        return in_array($this->resolve($node), $names, true);
    }

    public function resolve(Node $node): ?string
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
            $parentNode = $node->getAttribute(Attribute::PARENT_NODE);
            // is $variable::method(), unable to resolve $variable->class name
            if ($parentNode instanceof StaticCall) {
                return null;
            }
        }

        return (string) $node->name;
    }
}
