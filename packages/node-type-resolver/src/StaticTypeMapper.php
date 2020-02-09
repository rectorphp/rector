<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
use Rector\Core\Exception\NotImplementedException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\PhpDocTypeMapper;
use Rector\NodeTypeResolver\TypeMapper\PhpParserNodeMapper;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;

/**
 * Maps PhpParser <=> PHPStan <=> PHPStan doc <=> string type nodes between all possible formats
 */
final class StaticTypeMapper
{
    /**
     * @var PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var PhpParserNodeMapper
     */
    private $phpParserNodeMapper;

    /**
     * @var PhpDocTypeMapper
     */
    private $phpDocTypeMapper;

    public function __construct(
        PHPStanStaticTypeMapper $phpStanStaticTypeMapper,
        NodeNameResolver $nodeNameResolver,
        PhpParserNodeMapper $phpParserNodeMapper,
        PhpDocTypeMapper $phpDocTypeMapper
    ) {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpParserNodeMapper = $phpParserNodeMapper;
        $this->phpDocTypeMapper = $phpDocTypeMapper;
    }

    public function mapPHPStanTypeToPHPStanPhpDocTypeNode(Type $phpStanType): TypeNode
    {
        return $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($phpStanType);
    }

    /**
     * @return Identifier|Name|NullableType|PhpParserUnionType|null
     */
    public function mapPHPStanTypeToPhpParserNode(Type $phpStanType, ?string $kind = null): ?Node
    {
        return $this->phpStanStaticTypeMapper->mapToPhpParserNode($phpStanType, $kind);
    }

    public function mapPHPStanTypeToDocString(Type $phpStanType, ?Type $parentType = null): string
    {
        return $this->phpStanStaticTypeMapper->mapToDocString($phpStanType, $parentType);
    }

    public function mapPhpParserNodePHPStanType(Node $node): Type
    {
        return $this->phpParserNodeMapper->mapToPHPStanType($node);
    }

    public function mapPHPStanPhpDocTypeToPHPStanType(PhpDocTagValueNode $phpDocTagValueNode, Node $node): Type
    {
        if ($phpDocTagValueNode instanceof ReturnTagValueNode ||
            $phpDocTagValueNode instanceof ParamTagValueNode ||
            $phpDocTagValueNode instanceof VarTagValueNode
        ) {
            return $this->mapPHPStanPhpDocTypeNodeToPHPStanType($phpDocTagValueNode->type, $node);
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($phpDocTagValueNode));
    }

    /**
     * @return Identifier|Name|NullableType|null
     */
    public function mapStringToPhpParserNode(string $type): ?Node
    {
        if ($type === 'string') {
            return new Identifier('string');
        }

        if ($type === 'int') {
            return new Identifier('int');
        }

        if ($type === 'array') {
            return new Identifier('array');
        }

        if ($type === 'float') {
            return new Identifier('float');
        }

        if (Strings::contains($type, '\\') || ctype_upper($type[0])) {
            return new FullyQualified($type);
        }

        if (Strings::startsWith($type, '?')) {
            $nullableType = ltrim($type, '?');

            /** @var Identifier|Name $nameNode */
            $nameNode = $this->mapStringToPhpParserNode($nullableType);

            return new NullableType($nameNode);
        }

        if ($type === 'void') {
            return new Identifier('void');
        }

        throw new NotImplementedException(sprintf('%s for "%s"', __METHOD__, $type));
    }

    public function mapPHPStanPhpDocTypeNodeToPhpDocString(TypeNode $typeNode, Node $node): string
    {
        $phpStanType = $this->mapPHPStanPhpDocTypeNodeToPHPStanType($typeNode, $node);

        return $this->mapPHPStanTypeToDocString($phpStanType);
    }

    public function mapPHPStanPhpDocTypeNodeToPHPStanType(TypeNode $typeNode, Node $node): Type
    {
        $nameScope = $this->createNameScopeFromNode($node);

        return $this->phpDocTypeMapper->mapToPHPStanType($typeNode, $node, $nameScope);
    }

    /**
     * @see https://github.com/phpstan/phpstan-src/blob/8376548f76e2c845ae047e3010e873015b796818/src/Analyser/NameScope.php#L32
     */
    private function createNameScopeFromNode(Node $node): NameScope
    {
        $namespace = $node->getAttribute(AttributeKey::NAMESPACE_NAME);

        /** @var Use_[] $useNodes */
        $useNodes = (array) $node->getAttribute(AttributeKey::USE_NODES);

        $uses = $this->resolveUseNamesByAlias($useNodes);
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);

        return new NameScope($namespace, $uses, $className);
    }

    /***
     * @param Use_[] $useNodes
     * @return string[]
     */
    private function resolveUseNamesByAlias(array $useNodes): array
    {
        $useNamesByAlias = [];

        foreach ($useNodes as $useNode) {
            foreach ($useNode->uses as $useUse) {
                /** @var UseUse $useUse */
                $aliasName = $useUse->getAlias()->name;
                $useName = $this->nodeNameResolver->getName($useUse->name);
                if (! is_string($useName)) {
                    throw new ShouldNotHappenException();
                }

                $useNamesByAlias[$aliasName] = $useName;
            }
        }

        return $useNamesByAlias;
    }
}
