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
class AddGenericReturnTypeToRelationsRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add generic return type to relations in child of Illuminate\\Database\\Eloquent\\Model', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
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
        if (!$this->isObjectType($methodReturnType, new ObjectType('Illuminate\\Database\\Eloquent\\Relations\\Relation'))) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        // Return, if already has return type
        if ($node->getDocComment() !== null && $phpDocInfo->hasByName('return')) {
            return null;
        }
        $returnStatement = $this->betterNodeFinder->findFirstInFunctionLikeScoped($node, function (Node $subNode) : bool {
            return $subNode instanceof Return_;
        });
        if (!$returnStatement instanceof Return_) {
            return null;
        }
        $relationMethodCall = $this->betterNodeFinder->findFirstInstanceOf($returnStatement, MethodCall::class);
        if (!$relationMethodCall instanceof MethodCall) {
            return null;
        }
        $relatedClass = $this->getRelatedModelClassFromMethodCall($relationMethodCall);
        if ($relatedClass === null) {
            return null;
        }
        $phpDocInfo->addTagValueNode(new ReturnTagValueNode(new GenericTypeNode(new FullyQualifiedIdentifierTypeNode($methodReturnTypeName), [new FullyQualifiedIdentifierTypeNode($relatedClass)]), ''));
        return $node;
    }
    private function getRelatedModelClassFromMethodCall(MethodCall $methodCall) : ?string
    {
        $methodName = $methodCall->name;
        if (!$methodName instanceof Identifier) {
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
        if ($argType instanceof ConstantStringType) {
            return $argType->getValue();
        }
        if (!$argType instanceof GenericClassStringType) {
            return null;
        }
        $modelType = $argType->getGenericType();
        if (!$modelType instanceof ObjectType) {
            return null;
        }
        return $modelType->getClassName();
    }
    private function shouldSkipNode(ClassMethod $classMethod) : bool
    {
        if ($classMethod->stmts === null) {
            return \true;
        }
        $classLike = $this->betterNodeFinder->findParentType($classMethod, ClassLike::class);
        if (!$classLike instanceof ClassLike) {
            return \true;
        }
        if ($classLike instanceof Class_) {
            return !$this->isObjectType($classLike, new ObjectType('Illuminate\\Database\\Eloquent\\Model'));
        }
        return \false;
    }
}
