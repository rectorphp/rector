<?php

namespace Rector\Tests\BetterPhpDocParser\PhpDocInlineHtml\Source;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use ViewScopeRector\Inferer\Rocket\FileLocator;
use ViewScopeRector\Inferer\Rocket\ViewFileLocator;

class InlineHtmlRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Defines a @var on inline html.', [new CodeSample('', '')]);
    }

    public function getNodeTypes(): array
    {
        return [Node\Stmt\InlineHTML::class];
    }

    /**
     * @param Node\Stmt\InlineHTML $inlineHtml
     */
    public function refactor(Node $inlineHtml): ?Node
    {
        if (strpos($inlineHtml->value, '<h1>') !== false) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($inlineHtml);
            $phpDocInfo->addTagValueNode(new VarTagValueNode(new IdentifierTypeNode('string'), '$hello1', ''));
        }
        if (strpos($inlineHtml->value, '<h2>') !== false) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($inlineHtml);
            $phpDocInfo->addTagValueNode(new VarTagValueNode(new IdentifierTypeNode('string'), '$hello2', ''));
        }

        return null;
    }
}
