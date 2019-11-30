<?php

declare(strict_types=1);

namespace Rector\Php74\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;
use Rector\ValueObject\PhpVersionFeature;

/**
 * @source https://wiki.php.net/rfc/typed_properties_v2#proposal
 *
 * @see \Rector\Php74\Tests\Rector\Property\TypedPropertyRector\TypedPropertyRectorTest
 */
final class TypedPropertyRector extends AbstractRector
{
    /**
     * @var PropertyTypeInferer
     */
    private $propertyTypeInferer;

    public function __construct(PropertyTypeInferer $propertyTypeInferer)
    {
        $this->propertyTypeInferer = $propertyTypeInferer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Applies property type based on `@var` annotations and inference.',
            [
                new CodeSample(
                    <<<'PHP'
final class SomeClass
{
    /**
     * @var int
     */
    private count;
}
PHP
                    ,
                    <<<'PHP'
final class SomeClass
{
    /**
     * @var int
     */
     private int count;
}
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
            return null;
        }

        // type is already set → skip
        if ($node->type !== null) {
            return null;
        }

        $varType = $this->propertyTypeInferer->inferProperty($node);
        if ($varType instanceof MixedType) {
            return null;
        }

        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($varType, 'property');
        if ($propertyTypeNode === null) {
            return null;
        }

        $node->type = $propertyTypeNode;

        return $node;
    }
}
