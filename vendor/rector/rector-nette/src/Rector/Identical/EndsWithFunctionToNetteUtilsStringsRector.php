<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use Rector\Core\Rector\AbstractRector;
use Rector\Nette\NodeAnalyzer\StrlenEndsWithResolver;
use Rector\Nette\ValueObject\ContentExprAndNeedleExpr;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/nette/utils/blob/master/src/Utils/Strings.php
 * @see \Rector\Nette\Tests\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector\EndsWithFunctionToNetteUtilsStringsRectorTest
 */
final class EndsWithFunctionToNetteUtilsStringsRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Nette\NodeAnalyzer\StrlenEndsWithResolver
     */
    private $strlenEndsWithResolver;
    public function __construct(StrlenEndsWithResolver $strlenEndsWithResolver)
    {
        $this->strlenEndsWithResolver = $strlenEndsWithResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Identical::class, NotIdentical::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use Nette\\Utils\\Strings::endsWith() over bare string-functions', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function end($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = substr($content, -strlen($needle)) === $needle;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Nette\Utils\Strings;

class SomeClass
{
    public function end($needle)
    {
        $content = 'Hi, my name is Tom';
        $yes = Strings::endsWith($content, $needle);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node) : ?Node
    {
        $contentExprAndNeedleExpr = $this->strlenEndsWithResolver->resolveBinaryOpForFunction($node);
        if (!$contentExprAndNeedleExpr instanceof ContentExprAndNeedleExpr) {
            return null;
        }
        $staticCall = $this->nodeFactory->createStaticCall('Nette\\Utils\\Strings', 'endsWith', [$contentExprAndNeedleExpr->getContentExpr(), $contentExprAndNeedleExpr->getNeedleExpr()]);
        if ($node instanceof NotIdentical) {
            return new BooleanNot($staticCall);
        }
        return $staticCall;
    }
}
