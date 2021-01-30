<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Ramsey\Uuid\Uuid;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\Doctrine\DoctrineEntityManipulator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector\ChangeSetIdToUuidValueRectorTest
 */
final class ChangeSetIdToUuidValueRector extends AbstractRector
{
    /**
     * @var DoctrineEntityManipulator
     */
    private $doctrineEntityManipulator;

    public function __construct(DoctrineEntityManipulator $doctrineEntityManipulator)
    {
        $this->doctrineEntityManipulator = $doctrineEntityManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change set id to uuid values', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

class SomeClass
{
    public function run()
    {
        $buildingFirst = new Building();
        $buildingFirst->setId(1);
        $buildingFirst->setUuid(Uuid::fromString('a3bfab84-e207-4ddd-b96d-488151de9e96'));
    }
}

/**
 * @ORM\Entity
 */
class Building
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

class SomeClass
{
    public function run()
    {
        $buildingFirst = new Building();
        $buildingFirst->setId(Uuid::fromString('a3bfab84-e207-4ddd-b96d-488151de9e96'));
    }
}

/**
 * @ORM\Entity
 */
class Building
{
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        // A. try find "setUuid()" call on the same object later
        $setUuidMethodCall = $this->getSetUuidMethodCallOnSameVariable($node);
        if ($setUuidMethodCall !== null) {
            $node->args = $setUuidMethodCall->args;
            $this->removeNode($setUuidMethodCall);
            return $node;
        }

        // B. is the value constant reference?
        $argumentValue = $node->args[0]->value;
        if ($argumentValue instanceof ClassConstFetch) {
            $classConst = $this->nodeRepository->findClassConstByClassConstFetch($argumentValue);
            if (! $classConst instanceof ClassConst) {
                return null;
            }

            $constantValueStaticType = $this->getStaticType($classConst->consts[0]->value);

            // probably already uuid
            if ($constantValueStaticType instanceof StringType) {
                return null;
            }

            // update constant value
            $classConst->consts[0]->value = $this->createUuidStringNode();

            $node->args[0]->value = $this->nodeFactory->createStaticCall(Uuid::class, 'fromString', [$argumentValue]);

            return $node;
        }

        // C. set uuid from string with generated string
        $value = $this->nodeFactory->createStaticCall(Uuid::class, 'fromString', [$this->createUuidStringNode()]);
        $node->args[0]->value = $value;

        return $node;
    }

    private function shouldSkip(MethodCall $methodCall): bool
    {
        if (! $this->doctrineEntityManipulator->isMethodCallOnDoctrineEntity($methodCall, 'setId')) {
            return true;
        }

        if (! isset($methodCall->args[0])) {
            return true;
        }

        // already uuid static type
        return $this->isUuidType($methodCall->args[0]->value);
    }

    private function getSetUuidMethodCallOnSameVariable(MethodCall $methodCall): ?MethodCall
    {
        $parentNode = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Expression) {
            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        }

        if (! $parentNode instanceof Node) {
            return null;
        }

        $variableName = $this->getName($methodCall->var);
        if ($variableName === null) {
            return null;
        }

        /** @var ObjectType $variableType */
        $variableType = $this->getStaticType($methodCall->var);

        $methodCall = $this->betterNodeFinder->findFirst($parentNode, function (Node $node) use (
            $variableName,
            $variableType
        ): bool {
            if (! $node instanceof MethodCall) {
                return false;
            }

            if (! $this->isName($node->var, $variableName)) {
                return false;
            }

            if (! $this->isObjectType($node->var, $variableType)) {
                return false;
            }

            return $this->isName($node->name, 'setUuid');
        });

        if ($methodCall instanceof MethodCall) {
            return $methodCall;
        }

        return null;
    }

    private function createUuidStringNode(): String_
    {
        $uuidValue = Uuid::uuid4();
        $uuidValueString = $uuidValue->toString();

        return new String_($uuidValueString);
    }

    private function isUuidType(Expr $expr): bool
    {
        $argumentStaticType = $this->getStaticType($expr);

        // UUID is already set
        if (! $argumentStaticType instanceof ObjectType) {
            return false;
        }

        return $argumentStaticType->getClassName() === Uuid::class;
    }
}
