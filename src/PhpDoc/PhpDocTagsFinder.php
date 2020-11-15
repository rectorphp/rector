<?php

declare(strict_types=1);

namespace Rector\Core\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\SimplePhpDocParser\SimplePhpDocParser;

/**
 * @see \Rector\Core\Tests\PhpDoc\PhpDocTagsFinderTest
 */
final class PhpDocTagsFinder
{
    /**
     * @var SimplePhpDocParser
     */
    private $simplePhpDocParser;

    public function __construct(SimplePhpDocParser $simplePhpDocParser)
    {
        $this->simplePhpDocParser = $simplePhpDocParser;
    }

    /**
     * @return string[]
     */
    public function extractTrowsTypesFromDocBlock(string $docBlock): array
    {
        $simplePhpDocNode = $this->simplePhpDocParser->parseDocBlock($docBlock);
        return $this->resolveTypes($simplePhpDocNode->getThrowsTagValues());
    }

    /**
     * @return string[]
     */
    public function extractReturnTypesFromDocBlock(string $docBlock): array
    {
        $simplePhpDocNode = $this->simplePhpDocParser->parseDocBlock($docBlock);
        return $this->resolveTypes($simplePhpDocNode->getReturnTagValues());
    }

    /**
     * @param ThrowsTagValueNode[]|ReturnTagValueNode[] $tagValueNodes
     * @return string[]
     */
    private function resolveTypes(array $tagValueNodes): array
    {
        $types = [];

        foreach ($tagValueNodes as $tagValueNode) {
            $typeNode = $tagValueNode->type;
            if ($typeNode instanceof IdentifierTypeNode) {
                $types[] = $typeNode->name;
                continue;
            }

            if ($typeNode instanceof UnionTypeNode) {
                foreach ($typeNode->types as $unionedType) {
                    $types[] = $unionedType->name;
                }
            }
        }

        return $types;
    }
}
