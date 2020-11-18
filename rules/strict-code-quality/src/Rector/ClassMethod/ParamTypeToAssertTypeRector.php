<?php

declare(strict_types=1);

namespace Rector\StrictCodeQuality\Rector\ClassMethod;

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
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PHPStan\Type\ShortenedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\StrictCodeQuality\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\ParamTypeToAssertTypeRectorTest
 */
final class ParamTypeToAssertTypeRector extends AbstractRector
{
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
    /**
     * @param \A|\B $arg
     */
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
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        /** @var Type[] $paramTypes */
        $paramTypes = $phpDocInfo->getParamTypesByName();

        /** @var Param[] $params */
        $params = $node->getParams();

        if ($paramTypes === [] || $params === []) {
            return null;
        }

        $toBeProcessedTypes = [];
        foreach ($paramTypes as $key => $paramType) {
            if (! $paramType instanceof FullyQualifiedObjectType && ! $paramType instanceof UnionType && ! $paramType instanceof ShortenedObjectType) {
                continue;
            }

            $types = $this->getTypes($paramType);
            if ($this->isNotClassTypes($types)) {
                continue;
            }

            $toBeProcessedTypes = $this->getToBeProcessedTypes($params, $key, $types, $toBeProcessedTypes);
        }

        return $this->processAddTypeAssert($node, $toBeProcessedTypes);
    }

    /**
     * @return Type[]
     */
    private function getTypes(Type $type): array
    {
        return ! $type instanceof UnionType
            ? [$type]
            : $type->getTypes();
    }

    /**
     * @param Type[] $types
     */
    private function isNotClassTypes(array $types): bool
    {
        foreach ($types as $type) {
            if (! $type instanceof FullyQualifiedObjectType && ! $type instanceof ShortenedObjectType) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Param[] $params
     * @param Type[] $types
     * @param array<string, array<int, string>> $toBeProcessedTypes
     * @return array<string, array<int, string>>
     */
    private function getToBeProcessedTypes(array $params, string $key, array $types, array $toBeProcessedTypes): array
    {
        foreach ($params as $param) {
            $paramVarName = $this->getName($param->var);
            if (! $param->type instanceof FullyQualified || $key !== '$' . $paramVarName) {
                continue;
            }

            foreach ($types as $type) {
                $className = $type instanceof ShortenedObjectType
                    ? $type->getFullyQualifiedName()
                    : $type->getClassName();

                if (! is_a($className, $param->type->toString(), true) || $className === $param->type->toString()) {
                    continue 2;
                }

                $toBeProcessedTypes[$paramVarName][] = '\\' . $className;
            }
        }

        return $toBeProcessedTypes;
    }

    /**
     * @param array<string, array<int, string>> $toBeProcessedTypes
     */
    private function processAddTypeAssert(ClassMethod $classMethod, array $toBeProcessedTypes): ClassMethod
    {
        $assertStatements = [];
        foreach ($toBeProcessedTypes as $key => $toBeProcessedType) {
            $types = [];
            foreach ($toBeProcessedType as $keyType => $type) {
                $toBeProcessedType[$keyType] = sprintf('%s::class', $type);
                $types[] = new ArrayItem(new ConstFetch(new Name($toBeProcessedType[$keyType])));
            }

            if (count($types) > 1) {
                $assertStatements[] = new Expression(new StaticCall(new Name('\Webmozart\Assert\Assert'), 'isAnyOf', [
                    new Arg(new Variable($key)),
                    new Arg(new Array_($types)),
                ]));
            } else {
                $assertStatements[] = new Expression(new StaticCall(new Name('\Webmozart\Assert\Assert'), 'isAOf', [
                    new Arg(new Variable($key)),
                    new Arg(new ConstFetch(new Name($toBeProcessedType[0]))),
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
