<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/nette/utils/blob/master/src/Utils/Strings.php
 * @see \Rector\Nette\Tests\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector\SubstrStrlenFunctionToNetteUtilsStringsRectorTest
 */
final class SubstrStrlenFunctionToNetteUtilsStringsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const FUNCTION_TO_STATIC_METHOD = ['substr' => 'substring'];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use Nette\\Utils\\Strings over bare string-functions', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return substr($value, 0, 3);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return \Nette\Utils\Strings::substring($value, 0, 3);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach (self::FUNCTION_TO_STATIC_METHOD as $function => $staticMethod) {
            if (!$this->isName($node, $function)) {
                continue;
            }
            return $this->nodeFactory->createStaticCall('Nette\\Utils\\Strings', $staticMethod, $node->args);
        }
        return null;
    }
}
