<?php

declare (strict_types=1);
namespace Rector\Php81\NodeAnalyzer;

use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class EnumConstListClassDetector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function detect(Class_ $class) : bool
    {
        $classConstants = $class->getConstants();
        // must have at least 2 constants, otherwise probably not enum
        if (\count($classConstants) < 2) {
            return \false;
        }
        // only constants are allowed, nothing else
        if (\count($class->stmts) !== \count($classConstants)) {
            return \false;
        }
        // all constant must be public
        if (!$this->hasExclusivelyPublicClassConsts($classConstants)) {
            return \false;
        }
        // all constants must have exactly 1 value
        foreach ($classConstants as $classConstant) {
            if (\count($classConstant->consts) !== 1) {
                return \false;
            }
        }
        // only scalar values are allowed
        foreach ($classConstants as $classConstant) {
            $onlyConstConst = $classConstant->consts[0];
            if (!$onlyConstConst->value instanceof Scalar) {
                return \false;
            }
        }
        $uniqueTypeClasses = $this->resolveClassConstTypes($classConstants);
        // must be exactly 1 type
        return \count($uniqueTypeClasses) === 1;
    }
    /**
     * @param ClassConst[] $classConsts
     * @return array<class-string<Type>>
     */
    private function resolveClassConstTypes(array $classConsts) : array
    {
        $typeClasses = [];
        // all constants must have same type
        foreach ($classConsts as $classConst) {
            $const = $classConst->consts[0];
            $type = $this->nodeTypeResolver->getType($const->value);
            $typeClasses[] = \get_class($type);
        }
        return \array_unique($typeClasses);
    }
    /**
     * @param ClassConst[] $classConsts
     */
    private function hasExclusivelyPublicClassConsts(array $classConsts) : bool
    {
        foreach ($classConsts as $classConst) {
            if (!$classConst->isPublic()) {
                return \false;
            }
        }
        return \true;
    }
}
