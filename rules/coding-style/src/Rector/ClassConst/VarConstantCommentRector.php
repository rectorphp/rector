<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\VarConstantCommentRectorTest
 */
final class VarConstantCommentRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Constant should have a @var comment with type', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    const HI = 'hi';
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    /**
     * @var string
     */
    const HI = 'hi';
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
        return [ClassConst::class];
    }

    /**
     * @param ClassConst $node
     */
    public function refactor(Node $node): ?Node
    {
        if (count($node->consts) > 1) {
            return null;
        }

        $constStaticType = $this->getStaticType($node->consts[0]->value);
        if ($constStaticType instanceof MixedType) {
            return null;
        }

        // skip big constants
        if ($constStaticType instanceof ConstantArrayType && count($constStaticType->getValueTypes()) > 5) {
            return null;
        }

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        $phpDocInfo->changeVarType($constStaticType);

        return $node;
    }
}
