<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v10\v4;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\ErrorSuppress;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.4/Deprecation-90956-AlternativeFetchMethodsAndReportsForGeneralUtilitygetUrl.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v4\UseFileGetContentsForGetUrlRector\UseFileGetContentsForGetUrlRectorTest
 */
final class UseFileGetContentsForGetUrlRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Core\\Utility\\GeneralUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'getUrl')) {
            return null;
        }
        // Only calls with the url argument are rewritten
        if (\count($node->args) > 1) {
            return null;
        }
        $urlValue = $this->valueResolver->getValue($node->args[0]->value);
        if (null === $urlValue) {
            return null;
        }
        // Cannot rewrite for external urls
        if (\preg_match('#^(?:http|ftp)s?|s(?:ftp|cp):#', (string) $urlValue)) {
            return $this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Http\\RequestFactory')]), 'request', $node->args), 'getBody'), 'getContents');
        }
        return new ErrorSuppress($this->nodeFactory->createFuncCall('file_get_contents', $node->args));
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Rewirte Method Calls of GeneralUtility::getUrl("somefile.csv") to @file_get_contents', [new CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;

GeneralUtility::getUrl('some.csv');
$externalUrl = 'https://domain.com';
GeneralUtility::getUrl($externalUrl);

CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Http\RequestFactory;

@file_get_contents('some.csv');
$externalUrl = 'https://domain.com';
GeneralUtility::makeInstance(RequestFactory::class)->request($externalUrl)->getBody()->getContents();

CODE_SAMPLE
)]);
    }
}
