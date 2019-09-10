<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\PhpDocParser;

use PhpParser\Node\Param;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareParamTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class ParamPhpDocNodeFactory
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(NameResolver $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    /**
     * @param string[] $types
     */
    public function create(array $types, Param $param): AttributeAwarePhpDocTagNode
    {
        if (count($types) > 1) {
            $unionedTypes = [];
            foreach ($types as $type) {
                $unionedTypes[] = $this->createIdentifierTypeNode($type);
            }

            $typeNode = new UnionTypeNode($unionedTypes);
        } elseif (count($types) === 1) {
            $typeNode = $this->createIdentifierTypeNode($types[0]);
        } else {
            throw new ShouldNotHappenException(__METHOD__ . '() on line ' . __LINE__);
        }

        $arrayTypeNode = new ArrayTypeNode($typeNode);

        $paramTagValueNode = new AttributeAwareParamTagValueNode(
            $arrayTypeNode,
            $param->variadic,
            '$' . $this->nameResolver->getName($param),
            '',
            $param->byRef
        );

        return new AttributeAwarePhpDocTagNode('@param', $paramTagValueNode);
    }

    private function createIdentifierTypeNode(string $type): IdentifierTypeNode
    {
        if (class_exists($type)) {
            // FQN class name
            $type = '\\' . $type;
        }

        return new IdentifierTypeNode($type);
    }
}
