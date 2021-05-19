<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v1;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.1/Deprecation-75327-TSFE-csConvObjAndTSFE-csConv.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v1\TypoScriptFrontendControllerCharsetConverterRector\TypoScriptFrontendControllerCharsetConverterRectorTest
 */
final class TypoScriptFrontendControllerCharsetConverterRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const CHARSET_CONVERTER = 'charsetConverter';
    /**
     * @var string
     */
    private const CS_CONV = 'csConv';
    /**
     * @var \Ssch\TYPO3Rector\Helper\Typo3NodeResolver
     */
    private $typo3NodeResolver;
    public function __construct(\Ssch\TYPO3Rector\Helper\Typo3NodeResolver $typo3NodeResolver)
    {
        $this->typo3NodeResolver = $typo3NodeResolver;
    }
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
        if ($this->shouldSkip($node)) {
            return null;
        }
        if (!$this->isNames($node->name, [self::CS_CONV, 'conv_case'])) {
            return null;
        }
        if ($this->isName($node->name, self::CS_CONV)) {
            return $this->refactorMethodCsConv($node);
        }
        return $this->refactorCsConvObj($node);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor $TSFE->csConvObj and $TSFE->csConv()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$output = $GLOBALS['TSFE']->csConvObj->conv_case('utf-8', 'foobar', 'lower');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Charset\CharsetConverter;
$charsetConverter = GeneralUtility::makeInstance(CharsetConverter::class);
$output = $charsetConverter->conv_case('utf-8', 'foobar', 'lower');
CODE_SAMPLE
)]);
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $node) : bool
    {
        if ($this->typo3NodeResolver->isMethodCallOnGlobals($node, self::CS_CONV, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER)) {
            return \false;
        }
        if ($this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController'))) {
            return \false;
        }
        return !$this->typo3NodeResolver->isMethodCallOnPropertyOfGlobals($node, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER, 'csConvObj');
    }
    private function refactorMethodCsConv(\PhpParser\Node\Expr\MethodCall $node) : \PhpParser\Node
    {
        $from = isset($node->args[1]) ? $this->valueResolver->getValue($node->args[1]->value) : null;
        if ('' === $from || 'null' === $from || null === $from) {
            return $node->args[0]->value;
        }
        $this->addCharsetConverterNode($node);
        return $this->nodeFactory->createMethodCall(self::CHARSET_CONVERTER, 'conv', [$node->args[0], $this->nodeFactory->createMethodCall(self::CHARSET_CONVERTER, 'parse_charset', [$node->args[1]]), 'utf-8']);
    }
    private function addCharsetConverterNode(\PhpParser\Node\Expr\MethodCall $node) : void
    {
        $charsetConverterNode = new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::CHARSET_CONVERTER), $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Charset\\CharsetConverter')])));
        $this->addNodeBeforeNode($charsetConverterNode, $node);
    }
    private function refactorCsConvObj(\PhpParser\Node\Expr\MethodCall $node) : ?\PhpParser\Node
    {
        $this->addCharsetConverterNode($node);
        $methodName = $this->getName($node->name);
        if (null === $methodName) {
            return null;
        }
        return $this->nodeFactory->createMethodCall(self::CHARSET_CONVERTER, $methodName, $node->args);
    }
}
