<?php

declare(strict_types=1);

namespace Rector\Downgrade\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector\DowngradeTypedPropertyRectorTest
 */
final class DowngradeTypedPropertyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes property type definition from type definitions to `@var` annotations.', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    private string $property;
}
PHP
,
                <<<'PHP'
class SomeClass
{
    /**
    * @var string
    */
    private $property;
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
        if ($node->type === null) {
            return null;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            $phpDocInfo = $this->phpDocInfoFactory->createEmpty($node);
        }

        $newType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($node->type);
        $phpDocInfo->changeVarType($newType);
        $node->type = null;

        return $node;
    }
}
