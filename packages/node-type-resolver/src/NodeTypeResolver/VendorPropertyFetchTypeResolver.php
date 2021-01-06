<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Nop;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeCollector\NodeCollector\ParsedNodeCollector;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpdocParserPrinter\Parser\TokenAwarePhpDocParser;
use Rector\StaticTypeMapper\StaticTypeMapper;
use ReflectionProperty;

final class VendorPropertyFetchTypeResolver
{
    /**
     * @var ParsedNodeCollector
     */
    private $parsedNodeCollector;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var TokenAwarePhpDocParser
     */
    private $tokenAwarePhpDocParser;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    public function __construct(
        ParsedNodeCollector $parsedNodeCollector,
        NodeNameResolver $nodeNameResolver,
        TokenAwarePhpDocParser $tokenAwarePhpDocParser,
        StaticTypeMapper $staticTypeMapper
    ) {
        $this->parsedNodeCollector = $parsedNodeCollector;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->tokenAwarePhpDocParser = $tokenAwarePhpDocParser;
        $this->staticTypeMapper = $staticTypeMapper;
    }

    /**
     * Prevents circular reference
     */
    public function autowireVendorPropertyFetchTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function resolve(PropertyFetch $propertyFetch): Type
    {
        $varObjectType = $this->nodeTypeResolver->resolve($propertyFetch->var);
        if (! $varObjectType instanceof TypeWithClassName) {
            return new MixedType();
        }

        $class = $this->parsedNodeCollector->findClass($varObjectType->getClassName());
        if ($class !== null) {
            return new MixedType();
        }

        // 3rd party code
        $propertyName = $this->nodeNameResolver->getName($propertyFetch->name);
        if ($propertyName === null) {
            return new MixedType();
        }

        if (! property_exists($varObjectType->getClassName(), $propertyName)) {
            return new MixedType();
        }

        // property is used
        $reflectionProperty = new ReflectionProperty($varObjectType->getClassName(), $propertyName);
        $propertyDocComment = (string) $reflectionProperty->getDocComment();
        return $this->resolveTypeFromDocBlock($propertyDocComment);
    }

    private function resolveTypeFromDocBlock(string $propertyDocComment): Type
    {
        if ($propertyDocComment === '') {
            return new MixedType();
        }

        $phpDocNode = $this->tokenAwarePhpDocParser->parseDocBlock($propertyDocComment);
        $varTagValues = $phpDocNode->getVarTagValues();

        if (! isset($varTagValues[0])) {
            return new MixedType();
        }

        $typeNode = $varTagValues[0]->type;
        if (! $typeNode instanceof TypeNode) {
            return new MixedType();
        }

        return $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($typeNode, new Nop());
    }
}
