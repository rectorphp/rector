<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/** @see \Rector\Laravel\Tests\Rector\ClassMethod\AddGenericReturnTypeToRelationsRector\AddGenericReturnTypeToRelationsRectorTest */
class AddGenericReturnTypeToRelationsRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add generic return type to relations in child of Illuminate\\Database\\Eloquent\\Model', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use App\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use App\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    /** @return HasMany<Account> */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkipNode($node)) {
            return null;
        }
        $methodReturnType = $node->getReturnType();
        if ($methodReturnType === null) {
            return null;
        }
        $methodReturnTypeName = $this->getName($methodReturnType);
        if ($methodReturnTypeName === null) {
            return null;
        }
        if (!$this->isObjectType($methodReturnType, new \PHPStan\Type\ObjectType('Illuminate\\Database\\Eloquent\\Relations\\Relation'))) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return null;
        }
        // Return, if already has return type
        if ($node->getDocComment() !== null && $phpDocInfo->hasByName('return')) {
            return null;
        }
        $returnStatement = $this->betterNodeFinder->findFirstInFunctionLikeScoped($node, function (\PhpParser\Node $subNode) : bool {
            return $subNode instanceof \PhpParser\Node\Stmt\Return_;
        });
        if (!$returnStatement instanceof \PhpParser\Node\Stmt\Return_) {
            return null;
        }
        $relationMethodCall = $this->betterNodeFinder->findFirstInstanceOf($returnStatement, \PhpParser\Node\Expr\MethodCall::class);
        if (!$relationMethodCall instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        $relatedClass = $this->getRelatedModelClassFromMethodCall($relationMethodCall);
        if ($relatedClass === null) {
            return null;
        }
        $phpDocInfo->addTagValueNode(new \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode(new \PHPStan\PhpDocParser\Ast\Type\GenericTypeNode(new \Rector\BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode($methodReturnTypeName), [new \Rector\BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode($relatedClass)]), ''));
        return $node;
    }
    private function getRelatedModelClassFromMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?string
    {
        $methodName = $methodCall->name;
        if (!$methodName instanceof \PhpParser\Node\Identifier) {
            return null;
        }
        // Called method should be one of the Laravel's relation methods
        if (!\in_array($methodName->name, ['hasOne', 'hasOneThrough', 'morphOne', 'belongsTo', 'morphTo', 'hasMany', 'hasManyThrough', 'morphMany', 'belongsToMany', 'morphToMany', 'morphedByMany'], \true)) {
            return null;
        }
        if (\count($methodCall->getArgs()) < 1) {
            return null;
        }
        $argType = $this->getType($methodCall->getArgs()[0]->value);
        if ($argType instanceof \PHPStan\Type\Constant\ConstantStringType) {
            return $argType->getValue();
        }
        if (!$argType instanceof \PHPStan\Type\Generic\GenericClassStringType) {
            return null;
        }
        $modelType = $argType->getGenericType();
        if (!$modelType instanceof \PHPStan\Type\ObjectType) {
            return null;
        }
        return $modelType->getClassName();
    }
    private function shouldSkipNode(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if ($classMethod->stmts === null) {
            return \true;
        }
        $classLike = $this->betterNodeFinder->findParentType($classMethod, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return \true;
        }
        if ($classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return !$this->isObjectType($classLike, new \PHPStan\Type\ObjectType('Illuminate\\Database\\Eloquent\\Model'));
        }
        return \false;
    }
}
