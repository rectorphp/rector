<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\General;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\FilesFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ExtensionArchitecture/ConfigurationFiles/Index.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\General\ConvertImplicitVariablesToExplicitGlobalsRector\ConvertImplicitVariablesToExplicitGlobalsRectorTest
 */
final class ConvertImplicitVariablesToExplicitGlobalsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Helper\FilesFinder
     */
    private $filesFinder;
    public function __construct(\Ssch\TYPO3Rector\Helper\FilesFinder $filesFinder)
    {
        $this->filesFinder = $filesFinder;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert $TYPO3_CONF_VARS to $GLOBALS[\'TYPO3_CONF_VARS\']', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp']['foo'] = 'FooBarBaz->handle';
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp']['foo'] = 'FooBarBaz->handle';
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Variable::class];
    }
    /**
     * @param Variable $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isNames($node, ['TYPO3_CONF_VARS', 'TBE_MODULES'])) {
            return null;
        }
        $variableName = $this->getName($node);
        if (null === $variableName) {
            return null;
        }
        if (!$this->filesFinder->isExtLocalConf($this->file->getSmartFileInfo()) && !$this->filesFinder->isExtTables($this->file->getSmartFileInfo())) {
            return null;
        }
        return new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\Variable('GLOBALS'), new \PhpParser\Node\Scalar\String_($variableName));
    }
}
