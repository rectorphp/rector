<?php

declare(strict_types=1);

namespace Rector\StrictCodeQuality\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

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

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turn @param type to assert type', [
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

        $node = $this->processRemoveDocblock($node, $docCommentText);

        return $this->processAddTypeAssert($node, $anyOfTypes);
    }

    private function processRemoveDocblock(ClassMethod $classMethod, string $docCommentText): ClassMethod
    {
        $classMethod->setDocComment(new Doc($docCommentText));
        $expressionPhpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        $classMethod->setAttribute(AttributeKey::PHP_DOC_INFO, $expressionPhpDocInfo);

        return $classMethod;
    }

    /**
     * @param array<string, array<int, string>> $anyOfTypes
     */
    private function processAddTypeAssert(ClassMethod $classMethod, array $anyOfTypes): ClassMethod
    {
        $assertStatements = [];
        foreach ($anyOfTypes as $keyAnyOfType => $anyOfType) {
            $types = [];
            foreach ($anyOfType as $keyType => $type) {
                $anyOfType[$keyType] = sprintf('%s::class', $type);
                $types[] = new ArrayItem(new ConstFetch(new Name($anyOfType[$keyType])));
            }

            if (count($types) > 1) {
                $assertStatements[] = new Expression(new StaticCall(new Name('\Webmozart\Assert\Assert'), 'isAnyOf', [
                    new Arg(new Variable($keyAnyOfType)),
                    new Arg(new Array_($types)),
                ]));
            } else {
                $assertStatements[] = new Expression(new StaticCall(new Name('\Webmozart\Assert\Assert'), 'isAOf', [
                    new Arg(new Variable($keyAnyOfType)),
                    new Arg(new ConstFetch(new Name($anyOfType[0]))),
                ]));
            }
        }

        return $this->addStatements($classMethod, $assertStatements);
    }

    /**
     * @param array<int, Expression> $assertStatements
     */
    private function addStatements(ClassMethod $classMethod, array $assertStatements): ClassMethod
    {
        if (! isset($classMethod->stmts[0])) {
            foreach ($assertStatements as $assertStatement) {
                $classMethod->stmts[] = $assertStatement;
            }
        } else {
            foreach ($assertStatements as $assertStatement) {
                $this->addNodeBeforeNode($assertStatement, $classMethod->stmts[0]);
            }
        }

        return $classMethod;
    }
}
