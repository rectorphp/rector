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
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $typeValue = $this->valueResolver->getValue($node->getArgs()[1]->value);
        if (!\is_string($typeValue)) {
            return null;
        }
        $typeValue = \strtolower($typeValue);
        $variable = $node->getArgs()[0]->value;
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        // result of function or probably used
        if ($parentNode instanceof Expr || $parentNode instanceof Arg) {
            return null;
        }
        if (isset(self::TYPE_TO_CAST[$typeValue])) {
            $castClass = self::TYPE_TO_CAST[$typeValue];
            $castNode = new $castClass($variable);
            if ($parentNode instanceof Expression) {
                // bare expression? → assign
                return new Assign($variable, $castNode);
            }
            return $castNode;
        }
        if ($typeValue === 'null') {
            return new Assign($variable, $this->nodeFactory->createNull());
        }
        return $node;
    }
}
