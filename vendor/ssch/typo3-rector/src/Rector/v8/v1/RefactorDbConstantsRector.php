<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v1;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\ConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.1/Breaking-75454-TYPO3_dbConstantsRemoved.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v1\RefactorDbConstantsRector\RefactorDbConstantsRectorTest
 */
final class RefactorDbConstantsRector extends AbstractRector
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
        return [ConstFetch::class];
    }
    /**
     * @param ConstFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        $constantsName = $this->getName($node);
        if (null === $constantsName) {
            return null;
        }
        if (!\array_key_exists($constantsName, self::MAP_CONSTANTS_TO_GLOBALS)) {
            return null;
        }
        $globalKey = self::MAP_CONSTANTS_TO_GLOBALS[$constantsName];
        return new ArrayDimFetch(new ArrayDimFetch(new ArrayDimFetch(new ArrayDimFetch(new ArrayDimFetch(new Variable('GLOBALS'), new String_('TYPO3_CONF_VARS')), new String_('DB')), new String_('Connections')), new String_('Default')), new String_($globalKey));
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes TYPO3_db constants to $GLOBALS[\'TYPO3_CONF_VARS\'][\'DB\'][\'Connections\'][\'Default\'].', [new CodeSample(<<<'CODE_SAMPLE'
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
