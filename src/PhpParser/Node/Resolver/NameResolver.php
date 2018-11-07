<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Resolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Empty_;
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
    private $nameResolversPerNode = [];

    public function __construct()
    {
        $this->nameResolversPerNode[ClassConst::class] = function (ClassConst $classConstNode) {
            if (! count($classConstNode->consts)) {
                return null;
            }

            return $this->resolve($classConstNode->consts[0]);
        };

        $this->nameResolversPerNode[Property::class] = function (Property $propertyNode): ?string {
            if (! count($propertyNode->props)) {
                return null;
            }

            return $this->resolve($propertyNode->props[0]);
        };

        $this->nameResolversPerNode[Use_::class] = function (Use_ $useNode): ?string {
            if (! count($useNode->uses)) {
                return null;
            }

            return $this->resolve($useNode->uses[0]);
        };

        $this->nameResolversPerNode[Param::class] = function (Param $paramNode): ?string {
            return $this->resolve($paramNode->var);
        };

        $this->nameResolversPerNode[Name::class] = function (Name $nameNode): string {
            $resolvedName = $nameNode->getAttribute(Attribute::RESOLVED_NAME);
            if ($resolvedName instanceof FullyQualified) {
                return $resolvedName->toString();
            }

            return $nameNode->toString();
        };

        $this->nameResolversPerNode[Empty_::class] = function (): string {
            return 'empty';
        };
    }

    public function isName(Node $node, string $name): bool
    {
        return $this->resolve($node) === $name;
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

        return (string) $node->name;
    }
}
