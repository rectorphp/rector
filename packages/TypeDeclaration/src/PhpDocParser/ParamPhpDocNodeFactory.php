<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\PhpDocParser;

use PhpParser\Node\Param;
use PHPStan\Type\Type;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareParamTagValueNode;
use Rector\Core\PhpParser\Node\Resolver\NameResolver;
use Rector\NodeTypeResolver\StaticTypeMapper;

final class ParamPhpDocNodeFactory
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    public function __construct(NameResolver $nameResolver, StaticTypeMapper $staticTypeMapper)
    {
        $this->nameResolver = $nameResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }

    public function create(Type $type, Param $param): AttributeAwareParamTagValueNode
    {
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($type);

        return new AttributeAwareParamTagValueNode(
            $typeNode,
            $param->variadic,
            '$' . $this->nameResolver->getName($param),
            '',
            $param->byRef
        );
    }
}
