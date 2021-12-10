<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v3;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.3/Deprecation-89866-Global-TYPO3-information-related-constants.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v3\UseClassTypo3InformationRector\UseClassTypo3InformationRectorTest
 */
final class UseClassTypo3InformationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string[]
     */
    private const CONSTANTS_TO_REFACTOR = ['TYPO3_URL_GENERAL', 'TYPO3_URL_LICENSE', 'TYPO3_URL_EXCEPTION', 'TYPO3_URL_DONATE', 'TYPO3_URL_WIKI_OPCODECACHE'];
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\ConstFetch::class];
    }
    /**
     * @param ConstFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isNames($node->name, self::CONSTANTS_TO_REFACTOR)) {
            return null;
        }
        $nodeName = $this->getName($node->name);
        if ('TYPO3_URL_GENERAL' === $nodeName) {
            return $this->nodeFactory->createClassConstFetch('TYPO3\\CMS\\Core\\Information\\Typo3Information', 'URL_COMMUNITY');
        }
        if ('TYPO3_URL_LICENSE' === $nodeName) {
            return $this->nodeFactory->createClassConstFetch('TYPO3\\CMS\\Core\\Information\\Typo3Information', 'URL_LICENSE');
        }
        if ('TYPO3_URL_EXCEPTION' === $nodeName) {
            return $this->nodeFactory->createClassConstFetch('TYPO3\\CMS\\Core\\Information\\Typo3Information', 'URL_EXCEPTION');
        }
        if ('TYPO3_URL_DONATE' === $nodeName) {
            return $this->nodeFactory->createClassConstFetch('TYPO3\\CMS\\Core\\Information\\Typo3Information', 'URL_DONATE');
        }
        if ('TYPO3_URL_WIKI_OPCODECACHE' === $nodeName) {
            return $this->nodeFactory->createClassConstFetch('TYPO3\\CMS\\Core\\Information\\Typo3Information', 'URL_OPCACHE');
        }
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use class Typo3Information', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$urlGeneral = TYPO3_URL_GENERAL;
$urlLicense = TYPO3_URL_LICENSE;
$urlException = TYPO3_URL_EXCEPTION;
$urlDonate = TYPO3_URL_DONATE;
$urlOpcache = TYPO3_URL_WIKI_OPCODECACHE;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Information\Typo3Information;
$urlGeneral = Typo3Information::TYPO3_URL_GENERAL;
$urlLicense = Typo3Information::TYPO3_URL_LICENSE;
$urlException = Typo3Information::TYPO3_URL_EXCEPTION;
$urlDonate = Typo3Information::TYPO3_URL_DONATE;
$urlOpcache = Typo3Information::TYPO3_URL_WIKI_OPCODECACHE;
CODE_SAMPLE
)]);
    }
}
