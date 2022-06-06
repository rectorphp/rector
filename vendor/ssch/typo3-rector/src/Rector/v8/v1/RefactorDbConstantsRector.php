<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v1;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.1/Breaking-75454-TYPO3_dbConstantsRemoved.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v1\RefactorDbConstantsRector\RefactorDbConstantsRectorTest
 */
final class RefactorDbConstantsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const MAP_CONSTANTS_TO_GLOBALS = ['TYPO3_db' => 'dbname', 'TYPO3_db_username' => 'user', 'TYPO3_db_password' => 'password', 'TYPO3_db_host' => 'host'];
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
        if (!\array_key_exists($constantsName, self::MAP_CONSTANTS_TO_GLOBALS)) {
            return null;
        }
        $globalKey = self::MAP_CONSTANTS_TO_GLOBALS[$constantsName];
        return new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\Variable('GLOBALS'), new \PhpParser\Node\Scalar\String_('TYPO3_CONF_VARS')), new \PhpParser\Node\Scalar\String_('DB')), new \PhpParser\Node\Scalar\String_('Connections')), new \PhpParser\Node\Scalar\String_('Default')), new \PhpParser\Node\Scalar\String_($globalKey));
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes TYPO3_db constants to $GLOBALS[\'TYPO3_CONF_VARS\'][\'DB\'][\'Connections\'][\'Default\'].', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$database = TYPO3_db;
$username = TYPO3_db_username;
$password = TYPO3_db_password;
$host = TYPO3_db_host;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$database = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname'];
$username = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['user'];
$password = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['password'];
$host = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['host'];
CODE_SAMPLE
)]);
    }
}
