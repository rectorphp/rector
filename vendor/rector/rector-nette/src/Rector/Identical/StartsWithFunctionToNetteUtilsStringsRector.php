<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use Rector\Core\Rector\AbstractRector;
use Rector\Nette\NodeAnalyzer\StrlenStartsWithResolver;
use Rector\Nette\ValueObject\ContentExprAndNeedleExpr;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/nette/utils/blob/master/src/Utils/Strings.php
 *
 * @see \Rector\Nette\Tests\Rector\Identical\StartsWithFunctionToNetteUtilsStringsRector\StartsWithFunctionToNetteUtilsStringsRectorTest
 */
final class StartsWithFunctionToNetteUtilsStringsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Nette\NodeAnalyzer\StrlenStartsWithResolver
     */
    private $strlenStartsWithResolver;
    public function __construct(\Rector\Nette\NodeAnalyzer\StrlenStartsWithResolver $strlenStartsWithResolver)
    {
        $this->strlenStartsWithResolver = $strlenStartsWithResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\BinaryOp\Identical::class, \PhpParser\Node\Expr\BinaryOp\NotIdentical::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use Nette\\Utils\\Strings::startsWith() over bare string-functions', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
public function start($needle)
{
    $content = 'Hi, my name is Tom';
    $yes = substr($content, 0, strlen($needle)) === $needle;
}
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Nette\Utils\Strings;

class SomeClass
{
public function start($needle)
{
    $content = 'Hi, my name is Tom';
    $yes = Strings::startsWith($content, $needle);
}
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $contentExprAndNeedleExpr = $this->strlenStartsWithResolver->resolveBinaryOpForFunction($node, 'substr');
        if (!$contentExprAndNeedleExpr instanceof \Rector\Nette\ValueObject\ContentExprAndNeedleExpr) {
            return null;
        }
        $staticCall = $this->nodeFactory->createStaticCall('Nette\\Utils\\Strings', 'startsWith', [$contentExprAndNeedleExpr->getContentExpr(), $contentExprAndNeedleExpr->getNeedleExpr()]);
        if ($node instanceof \PhpParser\Node\Expr\BinaryOp\NotIdentical) {
            return new \PhpParser\Node\Expr\BooleanNot($staticCall);
        }
        return $staticCall;
    }
}
