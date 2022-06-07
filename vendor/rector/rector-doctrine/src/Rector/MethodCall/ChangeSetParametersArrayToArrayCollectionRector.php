<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/doctrine/orm/blob/2.7/UPGRADE.md#query-querybuilder-and-nativequery-parameters-bc-break
 * @see \Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetParametersArrayToArrayCollectionRector\ChangeSetParametersArrayToArrayCollectionRectorTest
 */
final class ChangeSetParametersArrayToArrayCollectionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer
     */
    private $arrayTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;
    public function __construct(ArrayTypeAnalyzer $arrayTypeAnalyzer, FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer)
    {
        $this->arrayTypeAnalyzer = $arrayTypeAnalyzer;
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkipMethodCall($node)) {
            return null;
        }
        /** @var Arg[] $methodArguments */
        $methodArguments = $node->args;
        if (\count($methodArguments) !== 1) {
            return null;
        }
        $firstArgument = $methodArguments[0];
        if (!$this->arrayTypeAnalyzer->isArrayType($firstArgument->value)) {
            return null;
        }
        unset($node->args);
        $new = $this->getNewArrayCollectionFromSetParametersArgument($firstArgument);
        $node->args = [new Arg($new)];
        return $node;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change array to ArrayCollection in setParameters method of query builder', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\EntityRepository;

class SomeRepository extends EntityRepository
{
    public function getSomething()
    {
        return $this
            ->createQueryBuilder('sm')
            ->select('sm')
            ->where('sm.foo = :bar')
            ->setParameters([
                'bar' => 'baz'
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Parameter;

class SomeRepository extends EntityRepository
{
    public function getSomething()
    {
        return $this
            ->createQueryBuilder('sm')
            ->select('sm')
            ->where('sm.foo = :bar')
            ->setParameters(new ArrayCollection([
                new  Parameter('bar', 'baz'),
            ]))
            ->getQuery()
            ->getResult()
        ;
    }
}
CODE_SAMPLE
)]);
    }
    private function shouldSkipMethodCall(MethodCall $methodCall) : bool
    {
        $classLike = $this->betterNodeFinder->findParentType($methodCall, ClassLike::class);
        if (!$classLike instanceof ClassLike) {
            return \true;
        }
        // one of the cases when we are in the repo and it's extended from EntityRepository
        if (!$this->isObjectType($classLike, new ObjectType('Doctrine\\ORM\\EntityRepository'))) {
            return \true;
        }
        if (!$this->isName($methodCall->name, 'setParameters')) {
            return \true;
        }
        // compare root variable
        $rootExpr = $this->fluentChainMethodCallNodeAnalyzer->resolveRootMethodCall($methodCall);
        if (!$rootExpr instanceof MethodCall) {
            return \true;
        }
        return !$this->isObjectType($rootExpr, new ObjectType('Doctrine\\ORM\\QueryBuilder'));
    }
    private function getNewArrayCollectionFromSetParametersArgument(Arg $arg) : New_
    {
        /** @var Array_ $arrayExpression */
        $arrayExpression = $arg->value;
        /** @var ArrayItem[] $firstArgumentArrayItems */
        $firstArgumentArrayItems = $arrayExpression->items;
        $arrayCollectionArrayArguments = [];
        foreach ($firstArgumentArrayItems as $firstArgumentArrayItem) {
            if (!$firstArgumentArrayItem->key instanceof String_ || !$firstArgumentArrayItem->value instanceof String_) {
                throw new ShouldNotHappenException();
            }
            $queryParameter = new New_(new FullyQualified('Doctrine\\ORM\\Query\\Parameter'));
            $queryParameter->args = [new Arg($firstArgumentArrayItem->key), new Arg($firstArgumentArrayItem->value)];
            $arrayCollectionArrayArguments[] = new ArrayItem($queryParameter);
        }
        $arrayCollection = new New_(new FullyQualified('Doctrine\\Common\\Collections\\ArrayCollection'));
        $arrayCollection->args = [new Arg(new Array_($arrayCollectionArrayArguments))];
        return $arrayCollection;
    }
}
