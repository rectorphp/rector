<?php

declare(strict_types=1);

namespace Rector\CodeQualityStrict\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodeQualityStrict\Tests\Rector\ClassMethod\ParamTypeToAssertTypeRector\ParamTypeToAssertTypeRectorTest
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
        if ($phpDocInfo === null) {
            return null;
        }

        /** @var Type[] $paramTypes */
        $paramTypes = $phpDocInfo->getParamTypesByName();
        if ($paramTypes === []) {
            return null;
        }

        $params = $node->getParams();
        if ($params === []) {
            return null;
        }

        $toBeProcessedTypes = [];
        foreach ($paramTypes as $key => $paramType) {
            if (! $this->isExclusivelyObjectType($paramType)) {
                continue;
            }

            $toBeProcessedTypes[$key] = $this->getToBeProcessedTypes($params, $key, $paramType);
        }

        return $this->processAddTypeAssert($node, $toBeProcessedTypes);
    }

    private function isExclusivelyObjectType(Type $type): bool
    {
        if ($type instanceof ObjectType) {
            return true;
        }

        if ($type instanceof UnionType) {
            foreach ($type->getTypes() as $unionedType) {
                if (! $this->isExclusivelyObjectType($unionedType)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param Param[] $params
     * @param ObjectType|UnionType<ObjectType> $types
     * @return array<string, array<int, string>>
     */
    private function getToBeProcessedTypes(array $params, string $key, Type $types): array
    {
        $assertionTypes = [];

        foreach ($params as $param) {
            if (! $this->isName($param->var, '$' . $key)) {
                continue;
            }

            if (! $param->type instanceof FullyQualified) {
                continue;
            }

            dump($types);
            die;

            foreach ($types as $type) {
                $className = $type instanceof ShortenedObjectType ? $type->getFullyQualifiedName() : $type->getClassName();

                // @todo refactor to types
                if (! is_a($className, $param->type->toString(), true) || $this->isName($param->type, $className)) {
                    continue 2;
                }

                $assertionTypes[] = '\\' . $className;
            }
        }

        return $assertionTypes;
    }

    /**
     * @param array<string, ObjectType[]> $toBeProcessedTypes
     */
    private function processAddTypeAssert(ClassMethod $classMethod, array $toBeProcessedTypes): ClassMethod
    {
        $assertStatements = [];
        foreach ($toBeProcessedTypes as $variableName => $requiredObjectTypes) {
            $classConstFetches = $this->createClassConstFetches($requiredObjectTypes);

            if (count($classConstFetches) > 1) {
                $args = [new Arg(new Variable($variableName)), new Arg(new Array_($classConstFetches))];
                $staticCall = $this->createStaticCall('Webmozart\Assert\Assert', 'isAnyOf', $args);
                $assertStatements[] = new Expression($staticCall);
            } else {
                $args = [new Arg(new Variable($variableName)), new Arg($classConstFetches[0])];
                $staticCall = $this->createStaticCall('Webmozart\Assert\Assert', 'isAOf', $args);
                $assertStatements[] = new Expression($staticCall);
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

    /**
     * @param ObjectType[] $objectTypes
     * @return ClassConstFetch[]
     */
    private function createClassConstFetches(array $objectTypes): array
    {
        $classConstTypes = [];
        foreach ($objectTypes as $objectType) {
            $classConstTypes[] = $this->createClassConstFetch($objectType->getClassName(), 'class');
        }

        return $classConstTypes;
    }
}
