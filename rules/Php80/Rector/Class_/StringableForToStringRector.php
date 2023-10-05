<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Cast\String_ as CastString_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
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
final class StringableForToStringRector extends AbstractRector implements MinPhpVersionInterface
{
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
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var string
     */
    private const STRINGABLE = 'Stringable';
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function __construct(FamilyRelationsAnalyzer $familyRelationsAnalyzer, ReturnTypeInferer $returnTypeInferer, ClassAnalyzer $classAnalyzer, BetterNodeFinder $betterNodeFinder)
    {
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->returnTypeInferer = $returnTypeInferer;
        $this->classAnalyzer = $classAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::STRINGABLE;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add `Stringable` interface to classes with `__toString()` method', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->classAnalyzer->isAnonymousClass($node)) {
            return null;
        }
        $toStringClassMethod = $node->getMethod(MethodName::TO_STRING);
        if (!$toStringClassMethod instanceof ClassMethod) {
            return null;
        }
        $this->hasChanged = \false;
        // warning, classes that implements __toString() will return Stringable interface even if they don't implemen it
        // reflection cannot be used for real detection
        $classLikeAncestorNames = $this->familyRelationsAnalyzer->getClassLikeAncestorNames($node);
        $isAncestorHasStringable = \in_array(self::STRINGABLE, $classLikeAncestorNames, \true);
        $returnType = $this->returnTypeInferer->inferFunctionLike($toStringClassMethod);
        if (!$returnType->isString()->yes()) {
            $this->processNotStringType($toStringClassMethod);
        }
        if (!$isAncestorHasStringable) {
            // add interface
            $node->implements[] = new FullyQualified(self::STRINGABLE);
            $this->hasChanged = \true;
        }
        // add return type
        if ($toStringClassMethod->returnType === null) {
            $toStringClassMethod->returnType = new Identifier('string');
            $this->hasChanged = \true;
        }
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    private function processNotStringType(ClassMethod $toStringClassMethod) : void
    {
        if ($toStringClassMethod->isAbstract()) {
            return;
        }
        $hasReturn = $this->betterNodeFinder->hasInstancesOfInFunctionLikeScoped($toStringClassMethod, Return_::class);
        if (!$hasReturn) {
            $emptyStringReturn = new Return_(new String_(''));
            $toStringClassMethod->stmts[] = $emptyStringReturn;
            $this->hasChanged = \true;
            return;
        }
        $this->traverseNodesWithCallable((array) $toStringClassMethod->stmts, function (Node $subNode) {
            if (!$subNode instanceof Return_) {
                return null;
            }
            if (!$subNode->expr instanceof Expr) {
                $subNode->expr = new String_('');
                return null;
            }
            $type = $this->nodeTypeResolver->getType($subNode->expr);
            if ($type->isString()->yes()) {
                return null;
            }
            $subNode->expr = new CastString_($subNode->expr);
            $this->hasChanged = \true;
            return null;
        });
    }
}
