<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PHPStan\PhpDoc\PHPUnit;

use RectorPrefix20220606\PHPStan\Analyser\NameScope;
use RectorPrefix20220606\PHPStan\PhpDoc\TypeNodeResolver;
use RectorPrefix20220606\PHPStan\PhpDoc\TypeNodeResolverAwareExtension;
use RectorPrefix20220606\PHPStan\PhpDoc\TypeNodeResolverExtension;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use RectorPrefix20220606\PHPStan\Type\NeverType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\TypeCombinator;
use RectorPrefix20220606\PHPStan\Type\TypeWithClassName;
use function array_key_exists;
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
        static $mockClassNames = ['PHPUnit_Framework_MockObject_MockObject' => \true, 'RectorPrefix20220606\\PHPUnit\\Framework\\MockObject\\MockObject' => \true, 'RectorPrefix20220606\\PHPUnit\\Framework\\MockObject\\Stub' => \true];
        $types = $this->typeNodeResolver->resolveMultiple($typeNode->types, $nameScope);
        foreach ($types as $type) {
            if (!$type instanceof TypeWithClassName) {
                continue;
            }
            if (array_key_exists($type->getClassName(), $mockClassNames)) {
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
