<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc\PHPUnit;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDoc\TypeNodeResolverAwareExtension;
use PHPStan\PhpDoc\TypeNodeResolverExtension;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use function array_key_exists;
use function count;
class MockObjectTypeNodeResolverExtension implements TypeNodeResolverExtension, TypeNodeResolverAwareExtension
{
    /** @var TypeNodeResolver */
    private $typeNodeResolver;
    public function setTypeNodeResolver(TypeNodeResolver $typeNodeResolver) : void
    {
        $this->typeNodeResolver = $typeNodeResolver;
    }
    public function getCacheKey() : string
    {
        return 'phpunit-v1';
    }
    public function resolve(TypeNode $typeNode, NameScope $nameScope) : ?Type
    {
        if (!$typeNode instanceof UnionTypeNode) {
            return null;
        }
        static $mockClassNames = ['PHPUnit_Framework_MockObject_MockObject' => \true, 'RectorPrefix202304\\PHPUnit\\Framework\\MockObject\\MockObject' => \true, 'RectorPrefix202304\\PHPUnit\\Framework\\MockObject\\Stub' => \true];
        $types = $this->typeNodeResolver->resolveMultiple($typeNode->types, $nameScope);
        foreach ($types as $type) {
            $classNames = $type->getObjectClassNames();
            if (count($classNames) !== 1) {
                continue;
            }
            if (array_key_exists($classNames[0], $mockClassNames)) {
                $resultType = TypeCombinator::intersect(...$types);
                if ($resultType instanceof NeverType) {
                    continue;
                }
                return $resultType;
            }
        }
        return null;
    }
}
