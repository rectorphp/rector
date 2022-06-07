<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\Cast\Object_;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://stackoverflow.com/questions/5577003/using-settype-in-php-instead-of-typecasting-using-brackets-what-is-the-differen/5577068#5577068
 *
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\SetTypeToCastRector\SetTypeToCastRectorTest
 */
final class SetTypeToCastRector extends AbstractRector
{
    /**
     * @var array<string, class-string<Cast>>
     */
    private const TYPE_TO_CAST = ['array' => Array_::class, 'bool' => Bool_::class, 'boolean' => Bool_::class, 'double' => Double::class, 'float' => Double::class, 'int' => Int_::class, 'integer' => Int_::class, 'object' => Object_::class, 'string' => String_::class];
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(ArgsAnalyzer $argsAnalyzer)
    {
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes settype() to (type) where possible', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($foo)
    {
        settype($foo, 'string');

        return settype($foo, 'integer');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($foo)
    {
        $foo = (string) $foo;

        return (int) $foo;
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'settype')) {
            return null;
        }
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($node->args, 1)) {
            return null;
        }
        /** @var Arg $secondArg */
        $secondArg = $node->args[1];
        $typeNode = $this->valueResolver->getValue($secondArg->value);
        if (!\is_string($typeNode)) {
            return null;
        }
        $typeNode = \strtolower($typeNode);
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($node->args, 0)) {
            return null;
        }
        /** @var Arg $firstArg */
        $firstArg = $node->args[0];
        $varNode = $firstArg->value;
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        // result of function or probably used
        if ($parentNode instanceof Expr || $parentNode instanceof Arg) {
            return null;
        }
        if (isset(self::TYPE_TO_CAST[$typeNode])) {
            $castClass = self::TYPE_TO_CAST[$typeNode];
            $castNode = new $castClass($varNode);
            if ($parentNode instanceof Expression) {
                // bare expression? → assign
                return new Assign($varNode, $castNode);
            }
            return $castNode;
        }
        if ($typeNode === 'null') {
            return new Assign($varNode, $this->nodeFactory->createNull());
        }
        return $node;
    }
}
