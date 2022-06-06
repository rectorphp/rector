<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Include_;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Deprecation-73067-DeprecateGeneralUtilityrequireOnceAndGeneralUtilityrequireFile.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v0\RequireMethodsToNativeFunctionsRector\RequireMethodsToNativeFunctionsRectorTest
 */
final class RequireMethodsToNativeFunctionsRector extends AbstractRector
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
        if (!$this->isNames($node->name, ['requireOnce', 'requireFile'])) {
            return null;
        }
        $file = $node->args[0]->value;
        if ($this->isName($node->name, 'requireFile')) {
            return new Include_($file, Include_::TYPE_REQUIRE);
        }
        return new Include_($file, Include_::TYPE_REQUIRE_ONCE);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor GeneralUtility::requireOnce and GeneralUtility::requireFile', [new CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;

GeneralUtility::requireOnce('somefile.php');
GeneralUtility::requireFile('some_other_file.php');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
require_once 'somefile.php';
require 'some_other_file.php';
CODE_SAMPLE
)]);
    }
}
