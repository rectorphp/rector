<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
/**
 * @see \Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\TraitTypeResolver\TraitTypeResolverTest
 */
final class TraitTypeResolver implements \Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface
{
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [\PhpParser\Node\Stmt\Trait_::class];
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : \PHPStan\Type\Type
    {
        $traitName = (string) $node->namespacedName;
        if (!$this->reflectionProvider->hasClass($traitName)) {
            return new \PHPStan\Type\MixedType();
        }
        $classReflection = $this->reflectionProvider->getClass($traitName);
        $types = [];
        $types[] = new \PHPStan\Type\ObjectType($traitName);
        foreach ($classReflection->getTraits() as $usedTraitReflection) {
            $types[] = new \PHPStan\Type\ObjectType($usedTraitReflection->getName());
        }
        if (\count($types) === 1) {
            return $types[0];
        }
        if (\count($types) > 1) {
            return new \PHPStan\Type\UnionType($types);
        }
        return new \PHPStan\Type\MixedType();
    }
}
