<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Cast\String_ as CastString_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\StringType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/stringable
 *
 * @see \Rector\Tests\Php80\Rector\Class_\StringableForToStringRector\StringableForToStringRectorTest
 */
final class StringableForToStringRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var string
     */
    private const STRINGABLE = 'Stringable';
    /**
     * @readonly
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    public function __construct(\Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer $familyRelationsAnalyzer, \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer $returnTypeInferer)
    {
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->returnTypeInferer = $returnTypeInferer;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::STRINGABLE;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add `Stringable` interface to classes with `__toString()` method', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __toString()
    {
        return 'I can stringz';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass implements Stringable
{
    public function __toString(): string
    {
        return 'I can stringz';
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
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $toStringClassMethod = $node->getMethod(\Rector\Core\ValueObject\MethodName::TO_STRING);
        if (!$toStringClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return null;
        }
        // warning, classes that implements __toString() will return Stringable interface even if they don't implemen it
        // reflection cannot be used for real detection
        $classLikeAncestorNames = $this->familyRelationsAnalyzer->getClassLikeAncestorNames($node);
        if (\in_array(self::STRINGABLE, $classLikeAncestorNames, \true)) {
            return null;
        }
        $returnType = $this->returnTypeInferer->inferFunctionLike($toStringClassMethod);
        if (!$returnType instanceof \PHPStan\Type\StringType) {
            $this->processNotStringType($toStringClassMethod);
        }
        // add interface
        $node->implements[] = new \PhpParser\Node\Name\FullyQualified(self::STRINGABLE);
        // add return type
        if ($toStringClassMethod->returnType === null) {
            $toStringClassMethod->returnType = new \PhpParser\Node\Name('string');
        }
        return $node;
    }
    private function processNotStringType(\PhpParser\Node\Stmt\ClassMethod $toStringClassMethod) : void
    {
        if ($toStringClassMethod->isAbstract()) {
            return;
        }
        $hasReturn = $this->betterNodeFinder->hasInstancesOfInFunctionLikeScoped($toStringClassMethod, \PhpParser\Node\Stmt\Return_::class);
        if (!$hasReturn) {
            $stmts = (array) $toStringClassMethod->stmts;
            \end($stmts);
            $lastKey = \key($stmts);
            $lastKey = $lastKey === null ? 0 : (int) $lastKey + 1;
            $toStringClassMethod->stmts[$lastKey] = new \PhpParser\Node\Stmt\Return_(new \PhpParser\Node\Scalar\String_(''));
            return;
        }
        $this->traverseNodesWithCallable((array) $toStringClassMethod->stmts, function (\PhpParser\Node $subNode) : void {
            if (!$subNode instanceof \PhpParser\Node\Stmt\Return_) {
                return;
            }
            if (!$subNode->expr instanceof \PhpParser\Node\Expr) {
                $subNode->expr = new \PhpParser\Node\Scalar\String_('');
                return;
            }
            $type = $this->nodeTypeResolver->getType($subNode->expr);
            if ($type instanceof \PHPStan\Type\StringType) {
                return;
            }
            $subNode->expr = new \PhpParser\Node\Expr\Cast\String_($subNode->expr);
        });
    }
}
