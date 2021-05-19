<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v5;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.5/Deprecation-78670-DeprecatedCharsetConverterMethods.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v5\CharsetConverterToMultiByteFunctionsRector\CharsetConverterToMultiByteFunctionsRectorTest
 */
final class CharsetConverterToMultiByteFunctionsRector extends \Rector\Core\Rector\AbstractRector
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
        if ($this->shouldSkip($node)) {
            return null;
        }
        $nodeName = $this->getName($node->name);
        if (null === $nodeName) {
            return null;
        }
        if ('strlen' === $nodeName) {
            return $this->toMultiByteStrlen($node);
        }
        if ('convCapitalize' === $nodeName) {
            return $this->toMultiByteConvertCase($node);
        }
        if ('substr' === $nodeName) {
            return $this->toMultiByteSubstr($node);
        }
        if ('conv_case' === $nodeName) {
            return $this->toMultiByteLowerUpperCase($node);
        }
        if ('utf8_strpos' === $nodeName) {
            return $this->toMultiByteStrPos($node);
        }
        if ('utf8_strrpos' === $nodeName) {
            return $this->toMultiByteStrrPos($node);
        }
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Move from CharsetConverter methods to mb_string functions', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
        use TYPO3\CMS\Core\Charset\CharsetConverter;
        use TYPO3\CMS\Core\Utility\GeneralUtility;
        $charsetConverter = GeneralUtility::makeInstance(CharsetConverter::class);
        $charsetConverter->strlen('utf-8', 'string');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
mb_strlen('string', 'utf-8');
CODE_SAMPLE
)]);
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $node) : bool
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Charset\\CharsetConverter'))) {
            return \true;
        }
        return !$this->isNames($node->name, ['strlen', 'convCapitalize', 'substr', 'conv_case', 'utf8_strpos', 'utf8_strrpos']);
    }
    private function toMultiByteConvertCase(\PhpParser\Node\Expr\MethodCall $node) : \PhpParser\Node\Expr\FuncCall
    {
        return $this->nodeFactory->createFuncCall('mb_convert_case', [$node->args[1], new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('MB_CASE_TITLE')), $node->args[0]]);
    }
    private function toMultiByteSubstr(\PhpParser\Node\Expr\MethodCall $node) : \PhpParser\Node\Expr\FuncCall
    {
        $start = $node->args[2] ?? $this->nodeFactory->createArg(0);
        $length = $node->args[3] ?? $this->nodeFactory->createNull();
        return $this->nodeFactory->createFuncCall('mb_substr', [$node->args[1], $start, $length, $node->args[0]]);
    }
    private function toMultiByteLowerUpperCase(\PhpParser\Node\Expr\MethodCall $node) : \PhpParser\Node\Expr\FuncCall
    {
        $methodCall = 'toLower' === $this->valueResolver->getValue($node->args[2]->value) ? 'mb_strtolower' : 'mb_strtoupper';
        return $this->nodeFactory->createFuncCall($methodCall, [$node->args[1], $node->args[0]]);
    }
    private function toMultiByteStrPos(\PhpParser\Node\Expr\MethodCall $node) : \PhpParser\Node\Expr\FuncCall
    {
        $offset = $node->args[2] ?? $this->nodeFactory->createArg(0);
        return $this->nodeFactory->createFuncCall('mb_strpos', [$node->args[0], $node->args[1], $offset, $this->nodeFactory->createArg('utf-8')]);
    }
    private function toMultiByteStrrPos(\PhpParser\Node\Expr\MethodCall $node) : \PhpParser\Node\Expr\FuncCall
    {
        return $this->nodeFactory->createFuncCall('mb_strrpos', [$node->args[0], $node->args[1], $this->nodeFactory->createArg('utf-8')]);
    }
    private function toMultiByteStrlen(\PhpParser\Node\Expr\MethodCall $node) : \PhpParser\Node\Expr\FuncCall
    {
        return $this->nodeFactory->createFuncCall('mb_strlen', \array_reverse($node->args));
    }
}
