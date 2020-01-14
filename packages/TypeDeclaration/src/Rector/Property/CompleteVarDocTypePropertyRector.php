<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\Property;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

/**
 * @see \Rector\TypeDeclaration\Tests\Rector\Property\CompleteVarDocTypePropertyRector\CompleteVarDocTypePropertyRectorTest
 */
final class CompleteVarDocTypePropertyRector extends AbstractRector
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
        return new RectorDefinition('Complete property `@var` annotations or correct the old ones', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
PHP
                ,
                <<<'PHP'
final class SomeClass
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
PHP
            ),
        ]);
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
        $propertyType = $this->propertyTypeInferer->inferProperty($node);
        /** @var \PhpParser\Comment\Doc $attribute */
        $attribute = $node->getAttribute('comments')[0];
        $attributeText = !is_null($attribute) ? $attribute->getText() : '';
        $isInheritdoc = Strings::contains(Strings::lower($attributeText), '@inheritdoc');

        if ($propertyType instanceof MixedType || $isInheritdoc) {
            return null;
        }

        $this->docBlockManipulator->changeVarTag($node, $propertyType);

        return $node;
    }
}
