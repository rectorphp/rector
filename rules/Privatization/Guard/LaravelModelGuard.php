<?php

declare (strict_types=1);
namespace Rector\Privatization\Guard;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Util\StringUtils;
/**
 * Guards against privatizing Laravel model attributes and scopes
 */
final class LaravelModelGuard
{
    /**
     * @readonly
     */
    private PhpAttributeAnalyzer $phpAttributeAnalyzer;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @var string
     * @see https://regex101.com/r/Dx0WN5/2
     */
    private const LARAVEL_MODEL_ATTRIBUTE_REGEX = '#^[gs]et.+Attribute$#';
    /**
     * @var string
     * @see https://regex101.com/r/hxOGeN/2
     */
    private const LARAVEL_MODEL_SCOPE_REGEX = '#^scope.+$#';
    public function __construct(PhpAttributeAnalyzer $phpAttributeAnalyzer, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function isProtectedMethod(ClassReflection $classReflection, ClassMethod $classMethod): bool
    {
        if (!$classReflection->is('Illuminate\Database\Eloquent\Model')) {
            return \false;
        }
        $name = (string) $this->nodeNameResolver->getName($classMethod->name);
        if ($this->isAttributeMethod($name, $classMethod)) {
            return \true;
        }
        return $this->isScopeMethod($name, $classMethod);
    }
    private function isAttributeMethod(string $name, ClassMethod $classMethod): bool
    {
        if (StringUtils::isMatch($name, self::LARAVEL_MODEL_ATTRIBUTE_REGEX)) {
            return \true;
        }
        if (!$classMethod->returnType instanceof Node) {
            return \false;
        }
        return $this->nodeTypeResolver->isObjectType($classMethod->returnType, new ObjectType('Illuminate\Database\Eloquent\Casts\Attribute'));
    }
    private function isScopeMethod(string $name, ClassMethod $classMethod): bool
    {
        if (StringUtils::isMatch($name, self::LARAVEL_MODEL_SCOPE_REGEX)) {
            return \true;
        }
        return $this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, 'Illuminate\Database\Eloquent\Attributes\Scope');
    }
}
