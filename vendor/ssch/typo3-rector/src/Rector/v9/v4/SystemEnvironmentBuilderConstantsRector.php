<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85793-SeveralConstantsFromSystemEnvironmentBuilder.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\SystemEnvironmentBuilderConstantsRector\SystemEnvironmentBuilderConstantsRectorTest
 */
final class SystemEnvironmentBuilderConstantsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const MAP_CONSTANTS_TO_STRING = ['TYPO3_URL_MAILINGLISTS' => 'http://lists.typo3.org/cgi-bin/mailman/listinfo', 'TYPO3_URL_DOCUMENTATION' => 'https://typo3.org/documentation/', 'TYPO3_URL_DOCUMENTATION_TSREF' => 'https://docs.typo3.org/typo3cms/TyposcriptReference/', 'TYPO3_URL_DOCUMENTATION_TSCONFIG' => 'https://docs.typo3.org/typo3cms/TSconfigReference/', 'TYPO3_URL_CONSULTANCY' => 'https://typo3.org/support/professional-services/', 'TYPO3_URL_CONTRIBUTE' => 'https://typo3.org/contribute/', 'TYPO3_URL_SECURITY' => 'https://typo3.org/teams/security/', 'TYPO3_URL_DOWNLOAD' => 'https://typo3.org/download/', 'TYPO3_URL_SYSTEMREQUIREMENTS' => 'https://typo3.org/typo3-cms/overview/requirements/', 'TAB' => "\t", 'NUL' => "\0", 'SUB' => '26', 'T3_ERR_SV_GENERAL' => 'ERROR_GENERAL', 'T3_ERR_SV_NOT_AVAIL' => 'ERROR_SERVICE_NOT_AVAILABLE', 'T3_ERR_SV_WRONG_SUBTYPE' => 'ERROR_WRONG_SUBTYPE', 'T3_ERR_SV_NO_INPUT' => 'ERROR_NO_INPUT', 'T3_ERR_SV_FILE_NOT_FOUND' => 'ERROR_FILE_NOT_FOUND', 'T3_ERR_SV_FILE_READ' => 'ERROR_FILE_NOT_READABLE', 'T3_ERR_SV_FILE_WRITE' => 'ERROR_FILE_NOT_WRITEABLE', 'T3_ERR_SV_PROG_NOT_FOUND' => 'ERROR_PROGRAM_NOT_FOUND', 'T3_ERR_SV_PROG_FAILED' => 'ERROR_PROGRAM_FAILED'];
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
        $constantsName = $this->getName($node);
        if (null === $constantsName) {
            return null;
        }
        if (!\array_key_exists($constantsName, self::MAP_CONSTANTS_TO_STRING)) {
            return null;
        }
        $value = self::MAP_CONSTANTS_TO_STRING[$constantsName];
        if (\strpos($constantsName, 'T3_ERR') !== \false) {
            return $this->nodeFactory->createClassConstFetch('TYPO3\\CMS\\Core\\Service\\AbstractService', $value);
        }
        if ('SUB' === $constantsName) {
            return $this->nodeFactory->createFuncCall('chr', [(int) $value]);
        }
        $string = new \PhpParser\Node\Scalar\String_($value);
        if ('TAB' === $constantsName || 'NUL' === $constantsName) {
            $string->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::KIND, \PhpParser\Node\Scalar\String_::KIND_DOUBLE_QUOTED);
        }
        return $string;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('GeneralUtility::verifyFilenameAgainstDenyPattern GeneralUtility::makeInstance(FileNameValidator::class)->isValid($filename)', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$var1 = TYPO3_URL_MAILINGLISTS;
$var2 = TYPO3_URL_DOCUMENTATION;
$var3 = TYPO3_URL_DOCUMENTATION_TSREF;
$var4 = TYPO3_URL_DOCUMENTATION_TSCONFIG;
$var5 = TYPO3_URL_CONSULTANCY;
$var6 = TYPO3_URL_CONTRIBUTE;
$var7 = TYPO3_URL_SECURITY;
$var8 = TYPO3_URL_DOWNLOAD;
$var9 = TYPO3_URL_SYSTEMREQUIREMENTS;
$nul = NUL;
$tab = TAB;
$sub = SUB;

$var10 = T3_ERR_SV_GENERAL;
$var11 = T3_ERR_SV_NOT_AVAIL;
$var12 = T3_ERR_SV_WRONG_SUBTYPE;
$var13 = T3_ERR_SV_NO_INPUT;
$var14 = T3_ERR_SV_FILE_NOT_FOUND;
$var15 = T3_ERR_SV_FILE_READ;
$var16 = T3_ERR_SV_FILE_WRITE;
$var17 = T3_ERR_SV_PROG_NOT_FOUND;
$var18 = T3_ERR_SV_PROG_FAILED;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Service\AbstractService;
$var1 = 'http://lists.typo3.org/cgi-bin/mailman/listinfo';
$var2 = 'https://typo3.org/documentation/';
$var3 = 'https://docs.typo3.org/typo3cms/TyposcriptReference/';
$var4 = 'https://docs.typo3.org/typo3cms/TSconfigReference/';
$var5 = 'https://typo3.org/support/professional-services/';
$var6 = 'https://typo3.org/contribute/';
$var7 = 'https://typo3.org/teams/security/';
$var8 = 'https://typo3.org/download/';
$var9 = 'https://typo3.org/typo3-cms/overview/requirements/';
$nul = "\0";
$tab = "\t";
$sub = chr(26);

$var10 = AbstractService::ERROR_GENERAL;
$var11 = AbstractService::ERROR_SERVICE_NOT_AVAILABLE;
$var12 = AbstractService::ERROR_WRONG_SUBTYPE;
$var13 = AbstractService::ERROR_NO_INPUT;
$var14 = AbstractService::ERROR_FILE_NOT_FOUND;
$var15 = AbstractService::ERROR_FILE_NOT_READABLE;
$var16 = AbstractService::ERROR_FILE_NOT_WRITEABLE;
$var17 = AbstractService::ERROR_PROGRAM_NOT_FOUND;
$var18 = AbstractService::ERROR_PROGRAM_FAILED;
CODE_SAMPLE
)]);
    }
}
