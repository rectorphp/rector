<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\ClassMethod;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\DateTimeToDateTimeInterfaceRectorTest
 */
final class DateTimeToDateTimeInterfaceRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const METHODS_RETURNING_CLASS_INSTANCE_MAP = [
        'add', 'modify', MethodName::SET_STATE, 'setDate', 'setISODate', 'setTime', 'setTimestamp', 'setTimezone', 'sub',
    ];

    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    public function __construct(PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes DateTime type-hint to DateTimeInterface', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass {
    public function methodWithDateTime(\DateTime $dateTime)
    {
        return true;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass {
    /**
     * @param \DateTime|\DateTimeImmutable $dateTime
     */
    public function methodWithDateTime(\DateTimeInterface $dateTime)
    {
        return true;
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
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::DATE_TIME_INTERFACE)) {
            return null;
        }

        $isModifiedNode = false;
        foreach ($node->getParams() as $param) {
            if (! $this->isDateTimeParam($param)) {
                continue;
            }

            $this->refactorParamTypeHint($param);
            $this->refactorParamDocBlock($param, $node);
            $this->refactorMethodCalls($param, $node);
            $isModifiedNode = true;
        }

        if (! $isModifiedNode) {
            return null;
        }

        return $node;
    }

    private function isDateTimeParam(Param $param): bool
    {
        return $this->nodeTypeResolver->isObjectTypeOrNullableObjectType($param, DateTime::class);
    }

    private function refactorParamTypeHint(Param $param): void
    {
        $fullyQualified = new FullyQualified(DateTimeInterface::class);
        if ($param->type instanceof NullableType) {
            $param->type = new NullableType($fullyQualified);
            return;
        }

        $param->type = $fullyQualified;
    }

    private function refactorParamDocBlock(Param $param, ClassMethod $classMethod): void
    {
        $types = [new ObjectType(DateTime::class), new ObjectType(DateTimeImmutable::class)];
        if ($param->type instanceof NullableType) {
            $types[] = new NullType();
        }

        $paramName = $this->getName($param->var);
        if ($paramName === null) {
            throw new ShouldNotHappenException();
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $this->phpDocTypeChanger->changeParamType($phpDocInfo, new UnionType($types), $param, $paramName);
    }

    private function refactorMethodCalls(Param $param, ClassMethod $classMethod): void
    {
        if ($classMethod->stmts === null) {
            return;
        }

        $this->traverseNodesWithCallable($classMethod->stmts, function (Node $node) use ($param): void {
            if (! ($node instanceof MethodCall)) {
                return;
            }

            $this->refactorMethodCall($param, $node);
        });
    }

    private function refactorMethodCall(Param $param, MethodCall $methodCall): void
    {
        $paramName = $this->getName($param->var);
        if ($paramName === null) {
            return;
        }
        if ($this->shouldSkipMethodCallRefactor($paramName, $methodCall)) {
            return;
        }

        $assign = new Assign(new Variable($paramName), $methodCall);

        /** @var Node $parent */
        $parent = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Arg) {
            $parent->value = $assign;
            return;
        }

        if (! $parent instanceof Expression) {
            return;
        }

        $parent->expr = $assign;
    }

    private function shouldSkipMethodCallRefactor(string $paramName, MethodCall $methodCall): bool
    {
        if (! $this->isName($methodCall->var, $paramName)) {
            return true;
        }

        if (! $this->isNames($methodCall->name, self::METHODS_RETURNING_CLASS_INSTANCE_MAP)) {
            return true;
        }

        $parentNode = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof \PhpParser\Node) {
            return true;
        }

        return $parentNode instanceof Assign;
    }
}
