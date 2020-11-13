<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UnionType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveParamReturnDocblockRector\RemoveParamReturnDocblockRectorTest
 */
final class RemoveParamReturnDocblockRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/Ue6Szm/6
     */
    private const PARAM_REGEX = '#^\s{0,}\*\s+@param\s+%s\s+\$%s$#msU';

    /**
     * @var string
     * @see https://regex101.com/r/Bw42yI/1
     */
    private const RETURN_REGEX = '#^\s{0,}\*\s+@return\s+%s$$#msU';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Remove @param and @return docblock with same type and no description on typed argument and return',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use stdClass;

class SomeClass
{
    /**
     * @param string $a
     * @param string $b description
     * @return stdClass
     */
    function foo(string $a, string $b): stdClass
    {

    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use stdClass;

class SomeClass
{
    /**
     * @param string $b description
     */
    function foo(string $a, string $b): stdClass
    {

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

        // process params
        /** @var Param[] $params */
        $params = $node->getParams();
        foreach ($params as $param) {
            if (! $param->type instanceof Identifier) {
                continue;
            }

            /** @var string $paramTypeName */
            $paramTypeName = $param->type->toString();

            /** @var string $paramTypeVarName */
            $paramTypeVarName = $param->var->name;

            $paramRegex = sprintf(self::PARAM_REGEX, $paramTypeName, $paramTypeVarName);
            if (Strings::match($docCommentText, $paramRegex)) {
                $docCommentText = Strings::replace($docCommentText, $paramRegex, '');
            }
        }

        $returnType = $node->returnType;
        if ($returnType instanceof NullableType || $returnType instanceof UnionType) {
            return $this->processUpdateDocblock($node, $text, $docCommentText);
        }

        if ($returnType instanceof FullyQualified) {
            $returnType = addslashes($returnType->toString());
        }

        $returnRegex = sprintf(self::RETURN_REGEX, $returnType);
        if (Strings::match($docCommentText, $returnRegex)) {
            $docCommentText = Strings::replace($docCommentText, $returnRegex, '');
        }

        return $this->processUpdateDocblock($node, $text, $docCommentText);
    }

    private function processUpdateDocblock(ClassMethod $classMethod, string $text, string $docCommentText): ?ClassMethod
    {
        if ($docCommentText === $text) {
            return null;
        }

        $classMethod->setDocComment(new Doc($docCommentText));
        $expressionPhpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        $classMethod->setAttribute(AttributeKey::PHP_DOC_INFO, $expressionPhpDocInfo);

        return $classMethod;
    }
}
