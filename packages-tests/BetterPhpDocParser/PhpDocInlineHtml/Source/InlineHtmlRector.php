<?php

namespace Rector\Tests\BetterPhpDocParser\PhpDocInlineHtml\Source;

use PhpParser\Node;
use PhpParser\Node\Stmt\InlineHTML;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\MixedType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class InlineHtmlRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Defines a @var on inline html.', [new CodeSample('', '')]);
    }

    public function getNodeTypes(): array
    {
        return [InlineHTML::class];
    }

    /**
     * @param InlineHTML $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! str_contains($node->value, '<h1>')) {
            return null;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        $currentVarTagValueNode = $phpDocInfo->getVarTagValueNode();
        if ($currentVarTagValueNode !== null) {
            return null;
        }

        $varTagValueNode = new VarTagValueNode(new IdentifierTypeNode('string'), '$hello1', '');
        $phpDocInfo->addTagValueNode($varTagValueNode);

        return $node;
    }
}
