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
use PHPStan\Type\TypeWithClassName;
use function array_key_exists;
class MockObjectTypeNodeResolverExtension implements \PHPStan\PhpDoc\TypeNodeResolverExtension, \PHPStan\PhpDoc\TypeNodeResolverAwareExtension
{
    /** @var TypeNodeResolver */
    private $typeNodeResolver;
    public function setTypeNodeResolver(\PHPStan\PhpDoc\TypeNodeResolver $typeNodeResolver) : void
    {
        $this->typeNodeResolver = $typeNodeResolver;
    }
    public function getCacheKey() : string
    {
        return 'phpunit-v1';
    }
    public function resolve(\PHPStan\PhpDocParser\Ast\Type\TypeNode $typeNode, \PHPStan\Analyser\NameScope $nameScope) : ?\PHPStan\Type\Type
    {
        if (!$typeNode instanceof \PHPStan\PhpDocParser\Ast\Type\UnionTypeNode) {
            return null;
        }
        static $mockClassNames = ['PHPUnit_Framework_MockObject_MockObject' => \true, 'RectorPrefix20220418\\PHPUnit\\Framework\\MockObject\\MockObject' => \true, 'RectorPrefix20220418\\PHPUnit\\Framework\\MockObject\\Stub' => \true];
        $types = $this->typeNodeResolver->resolveMultiple($typeNode->types, $nameScope);
        foreach ($types as $type) {
            if (!$type instanceof \PHPStan\Type\TypeWithClassName) {
                continue;
            }
            if (\array_key_exists($type->getClassName(), $mockClassNames)) {
                $resultType = \PHPStan\Type\TypeCombinator::intersect(...$types);
                if ($resultType instanceof \PHPStan\Type\NeverType) {
                    continue;
                }
                return $resultType;
            }
        }
        return null;
    }
}
