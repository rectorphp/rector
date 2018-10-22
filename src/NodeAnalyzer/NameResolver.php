<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use Rector\NodeTypeResolver\Node\Attribute;

final class NameResolver
{
    /**
     * @var callable[]
     */
    private $nameResolvers = [];

    public function __construct()
    {
        $this->nameResolvers[ClassConst::class] = function (ClassConst $classConstNode) {
            if (! count($classConstNode->consts)) {
                return null;
            }

            return $this->resolve($classConstNode->consts[0]);
        };

        $this->nameResolvers[Property::class] = function (Property $propertyNode) {
            if (! count($propertyNode->props)) {
                return null;
            }

            return $this->resolve($propertyNode->props[0]);
        };

        $this->nameResolvers[Use_::class] = function (Use_ $useNode) {
            if (! count($useNode->uses)) {
                return null;
            }

            return $this->resolve($useNode->uses[0]);
        };

        $this->nameResolvers[Name::class] = function (Name $nameNode) {
            $resolvedName = $nameNode->getAttribute(Attribute::RESOLVED_NAME);
            if ($resolvedName instanceof FullyQualified) {
                return $resolvedName->toString();
            }

            return $nameNode->toString();
        };
    }

    public function isName(Node $node, string $name): bool
    {
        return $this->resolve($node) === $name;
    }

    public function resolve(Node $node): ?string
    {
        foreach ($this->nameResolvers as $type => $nameResolver) {
            if (is_a($node, $type, true)) {
                return $nameResolver($node);
            }
        }

        if ($node instanceof Param) {
            return $this->resolve($node->var);
        }

        if (! property_exists($node, 'name')) {
            return null;
        }

        // unable to resolve
        if ($node->name instanceof Expr) {
            return null;
        }

        return (string) $node->name;
    }
}
