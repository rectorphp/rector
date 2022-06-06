<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v4;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85408-TemplateServiceInitDeprecated.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\RemoveInitMethodTemplateServiceRector\RemoveInitMethodTemplateServiceRectorTest
 */
final class RemoveInitMethodTemplateServiceRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Core\\TypoScript\\TemplateService'))) {
            return null;
        }
        if (!$this->isName($node->name, 'init')) {
            return null;
        }
        $this->removeNode($node);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove method call init of class TemplateService', [new CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
$templateService = GeneralUtility::makeInstance(TemplateService::class);
$templateService->init();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
$templateService = GeneralUtility::makeInstance(TemplateService::class);
CODE_SAMPLE
)]);
    }
}
