<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v2;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.2/Deprecation-84171-AddingGeneralUtilitygetUrlRequestHeadersAsNon-associativeArrayAreDeprecated.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v2\GeneralUtilityGetUrlRequestHeadersRector\GeneralUtilityGetUrlRequestHeadersRectorTest
 */
final class GeneralUtilityGetUrlRequestHeadersRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor GeneralUtility::getUrl() request headers in a associative way', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
GeneralUtility::getUrl('https://typo3.org', 1, ['Content-Language: de-DE']);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
GeneralUtility::getUrl('https://typo3.org', 1, ['Content-Language' => 'de-DE']);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Utility\\GeneralUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'getUrl')) {
            return null;
        }
        if (!isset($node->args[2])) {
            return null;
        }
        $requestHeadersArgumentValue = $node->args[2]->value;
        $requestHeaders = $this->valueResolver->getValue($requestHeadersArgumentValue);
        if (!\is_array($requestHeaders)) {
            return null;
        }
        $newHeaders = $this->buildHeaders($requestHeaders);
        if ([] === $newHeaders) {
            return null;
        }
        $newHeadersNode = $this->nodeFactory->createArray($newHeaders);
        $node->args[2]->value = $newHeadersNode;
        return $node;
    }
    /**
     * @param mixed[] $requestHeaders
     * @return mixed[]
     */
    private function buildHeaders(array $requestHeaders) : array
    {
        $newHeaders = [];
        foreach ($requestHeaders as $requestHeader) {
            $parts = \preg_split('#:[ \\t]*#', $requestHeader, 2, \PREG_SPLIT_NO_EMPTY);
            if (\false === $parts) {
                continue;
            }
            if (2 !== \count($parts)) {
                continue;
            }
            $key =& $parts[0];
            $value =& $parts[1];
            if (\array_key_exists($key, $newHeaders)) {
                if (\is_array($newHeaders[$key])) {
                    $newHeaders[$key][] = $value;
                } else {
                    $prevValue =& $newHeaders[$key];
                    $newHeaders[$key] = [$prevValue, $value];
                }
            } else {
                $newHeaders[$key] = $value;
            }
        }
        return $newHeaders;
    }
}
