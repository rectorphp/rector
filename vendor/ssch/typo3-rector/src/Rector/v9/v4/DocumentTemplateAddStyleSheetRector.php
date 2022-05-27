<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85735-MethodAndPropertyInDocumentTemplate.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\DocumentTemplateAddStyleSheetRector\DocumentTemplateAddStyleSheetRectorTest
 */
final class DocumentTemplateAddStyleSheetRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate'))) {
            return null;
        }
        if (!$this->isName($node->name, 'addStyleSheet')) {
            return null;
        }
        $args = $node->args;
        if (!isset($args[0], $args[1])) {
            return null;
        }
        $href = $this->valueResolver->getValue($args[1]->value);
        $title = isset($args[2]) ? $this->valueResolver->getValue($args[2]->value) : '';
        $relation = isset($args[3]) ? $this->valueResolver->getValue($args[3]->value) : 'stylesheet';
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Page\\PageRenderer')]), 'addCssFile', [$href, $relation, 'screen', $title]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use PageRenderer::addCssFile instead of DocumentTemplate::addStyleSheet() ', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$documentTemplate = GeneralUtility::makeInstance(DocumentTemplate::class);
$documentTemplate->addStyleSheet('foo', 'foo.css');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
GeneralUtility::makeInstance(PageRenderer::class)->addCssFile('foo.css', 'stylesheet', 'screen', '');
CODE_SAMPLE
)]);
    }
}
