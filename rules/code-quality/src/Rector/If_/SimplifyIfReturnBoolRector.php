<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\Comment\MergedNodeCommentPreserver;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\StaticTypeAnalyzer;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;

/**
 * @see \Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\SimplifyIfReturnBoolRectorTest
 */
final class SimplifyIfReturnBoolRector extends AbstractRector
{
    /**
     * @var StaticTypeAnalyzer
     */
    private $staticTypeAnalyzer;

    /**
     * @var MergedNodeCommentPreserver
     */
    private $mergedNodeCommentPreserver;

    /**
     * @var TypeUnwrapper
     */
    private $typeUnwrapper;

    public function __construct(
        MergedNodeCommentPreserver $mergedNodeCommentPreserver,
        StaticTypeAnalyzer $staticTypeAnalyzer,
        TypeUnwrapper $typeUnwrapper
    ) {
        $this->mergedNodeCommentPreserver = $mergedNodeCommentPreserver;
        $this->typeUnwrapper = $typeUnwrapper;
        $this->staticTypeAnalyzer = $staticTypeAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Shortens if return false/true to direct return', [
            new CodeSample(
                <<<'CODE_SAMPLE'
if (strpos($docToken->getContent(), "\n") === false) {
    return true;
}

return false;
CODE_SAMPLE
                ,
                'return strpos($docToken->getContent(), "\n") === false;'
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var Return_ $ifInnerNode */
        $ifInnerNode = $node->stmts[0];

        /** @var Return_ $nextNode */
        $nextNode = $node->getAttribute(AttributeKey::NEXT_NODE);

        /** @var Node $innerIfInnerNode */
        $innerIfInnerNode = $ifInnerNode->expr;

        if ($this->isTrue($innerIfInnerNode)) {
            $newReturnNode = $this->processReturnTrue($node, $nextNode);
        } elseif ($this->isFalse($innerIfInnerNode)) {
            $newReturnNode = $this->processReturnFalse($node, $nextNode);
        } else {
            return null;
        }

        if ($newReturnNode === null) {
            return null;
        }

        $this->mergedNodeCommentPreserver->keepComments($newReturnNode, $node, $ifInnerNode, $nextNode, $newReturnNode);
        $this->removeNode($nextNode);

        return $newReturnNode;
    }

    private function shouldSkip(If_ $if): bool
    {
        if ($if->elseifs !== []) {
            return true;
        }

        if ($this->isElseSeparatedThenIf($if)) {
            return true;
        }

        if (! $this->isIfWithSingleReturnExpr($if)) {
            return true;
        }

        /** @var Return_ $ifInnerNode */
        $ifInnerNode = $if->stmts[0];

        /** @var Expr $returnedExpr */
        $returnedExpr = $ifInnerNode->expr;

        if (! $this->isBool($returnedExpr)) {
            return true;
        }

        $nextNode = $if->getAttribute(AttributeKey::NEXT_NODE);
        if (! $nextNode instanceof Return_ || $nextNode->expr === null) {
            return true;
        }

        // negate + negate → skip for now
        if ($this->isFalse($returnedExpr) && Strings::contains($this->print($if->cond), '!=')) {
            return true;
        }

        return ! $this->isBool($nextNode->expr);
    }

    private function processReturnTrue(If_ $if, Return_ $nextReturnNode): Return_
    {
        if ($if->cond instanceof BooleanNot && $nextReturnNode->expr !== null && $this->isTrue(
            $nextReturnNode->expr
        )) {
            return new Return_($this->boolCastOrNullCompareIfNeeded($if->cond->expr));
        }

        return new Return_($this->boolCastOrNullCompareIfNeeded($if->cond));
    }

    private function processReturnFalse(If_ $if, Return_ $nextReturnNode): ?Return_
    {
        if ($if->cond instanceof Identical) {
            return new Return_($this->boolCastOrNullCompareIfNeeded(
                new NotIdentical($if->cond->left, $if->cond->right)
            ));
        }

        if ($nextReturnNode->expr === null) {
            return null;
        }

        if (! $this->isTrue($nextReturnNode->expr)) {
            return null;
        }

        if ($if->cond instanceof BooleanNot) {
            return new Return_($this->boolCastOrNullCompareIfNeeded($if->cond->expr));
        }

        return new Return_($this->boolCastOrNullCompareIfNeeded(new BooleanNot($if->cond)));
    }

    /**
     * Matches: "else if"
     */
    private function isElseSeparatedThenIf(If_ $if): bool
    {
        if ($if->else === null) {
            return false;
        }

        if (count($if->else->stmts) !== 1) {
            return false;
        }

        $onlyStmt = $if->else->stmts[0];

        return $onlyStmt instanceof If_;
    }

    private function isIfWithSingleReturnExpr(If_ $if): bool
    {
        if (count($if->stmts) !== 1) {
            return false;
        }

        if ($if->elseifs !== []) {
            return false;
        }

        $ifInnerNode = $if->stmts[0];
        if (! $ifInnerNode instanceof Return_) {
            return false;
        }

        // return must have value
        return $ifInnerNode->expr !== null;
    }

    private function boolCastOrNullCompareIfNeeded(Expr $expr): Expr
    {
        if ($this->isNullableType($expr)) {
            $exprStaticType = $this->getStaticType($expr);
            // if we remove null type, still has to be trueable
            if ($exprStaticType instanceof UnionType) {
                $unionTypeWithoutNullType = $this->typeUnwrapper->removeNullTypeFromUnionType($exprStaticType);
                if ($this->staticTypeAnalyzer->isAlwaysTruableType($unionTypeWithoutNullType)) {
                    return new NotIdentical($expr, $this->createNull());
                }
            } elseif ($this->staticTypeAnalyzer->isAlwaysTruableType($exprStaticType)) {
                return new NotIdentical($expr, $this->createNull());
            }
        }

        if (! $this->isBoolCastNeeded($expr)) {
            return $expr;
        }

        return new Bool_($expr);
    }

    private function isBoolCastNeeded(Expr $expr): bool
    {
        if ($expr instanceof BooleanNot) {
            return false;
        }

        if ($this->isStaticType($expr, BooleanType::class)) {
            return false;
        }

        return ! $expr instanceof BinaryOp;
    }
}
