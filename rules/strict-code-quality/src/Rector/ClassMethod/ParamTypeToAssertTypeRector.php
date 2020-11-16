<?php

declare(strict_types=1);

namespace Rector\StrictCodeQuality\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use PhpParser\Node\Name\FullyQualified;

/**
 * @see \Rector\StrictCodeQuality\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\ParamTypeToAssertTypeRectorTest
 */
final class ParamTypeToAssertTypeRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/IQCUYC/1
     */
    private const PARAM_REGEX = '#^\s{0,}\*\s+@param\s+(.*)\s+\$%s$$#msU';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turn @param type to assert type', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param \A|\B $arg
     */
    public function run($arg)
    {

    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($arg)
    {
        \Webmozart\Assert\Assert::isAnyOf($arg, [\A::class, \B::class]);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $docComment = $node->getDocComment();
        if (! $docComment instanceof Doc) {
            return null;
        }

        $text = $docComment->getText();
        $docCommentText = $text;

        /** @var Param[] $params */
        $params = $node->getParams();

        $anyOfTypes = [];
        foreach ($params as $param) {
            if (! $param->type instanceof FullyQualified) {
                continue;
            }

            $paramTypeVarName = $this->getName($param->var);
            $paramRegex = sprintf(self::PARAM_REGEX, $paramTypeVarName);

            $matches = Strings::match($docCommentText, $paramRegex);
            if ($matches) {
                $anyOfTypes[$paramTypeVarName] = explode('|', $matches[1]);
                $docCommentText = Strings::replace($docCommentText, $paramRegex, '');
            }
        }

        if ($docCommentText === $text) {
            return null;
        }

        $node->setDocComment(new Doc($docCommentText));
        $expressionPhpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        $node->setAttribute(AttributeKey::PHP_DOC_INFO, $expressionPhpDocInfo);

        foreach ($anyOfTypes as $key => $anyOfType) {
            foreach ($anyOfType as $key2 => $type) {
                $anyOfType[$key2] = new ConstFetch(new Name($type . '::class'));
            }

            if (! isset($node->stmts[0])) {
                $node->stmts[] = new Expression(new StaticCall(new Name('\Webmozart\Assert\Assert'), 'isAnyOf', [
                    new Arg(new Variable($key)),
                    new Arg(new Array_($anyOfType)),
                ]));
            } else {
                $this->addNodeBeforeNode(
                    new Expression(new StaticCall(new Name('\Webmozart\Assert\Assert'), 'isAnyOf', [
                        new Arg(new Variable($key)),
                        new Arg(new Array_($anyOfType)),
                    ])),
                    $node->stmts[0]
                );
            }
        }

        return $node;
    }
}
