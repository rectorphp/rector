<?php declare(strict_types=1);

namespace Rector\Doctrine\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Ramsey\Uuid\Uuid;
use Rector\Doctrine\ValueObject\DoctrineClass;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector\ChangeSetIdToUuidValueRectorTest
 */
final class ChangeSetIdToUuidValueRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change set id to uuid values', [
            new CodeSample(
                <<<'PHP'
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
PHP
                ,
                <<<'PHP'
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
PHP
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
        $setUuidCallOnSameVariable = $this->getSetUuidMethodCallOnSameVariable($node);
        if ($setUuidCallOnSameVariable) {
            $node->args = $setUuidCallOnSameVariable->args;
            $this->removeNode($setUuidCallOnSameVariable);
            return $node;
        }

        // already uuid static type
        if ($this->isUuidType($node->args[0]->value)) {
            return null;
        }

        // B. set uuid from string with generated string
        $uuidValue = Uuid::uuid4();
        $uuidValueString = $uuidValue->toString();

        $value = $this->createStaticCall(Uuid::class, 'fromString', [new String_($uuidValueString)]);
        $node->args[0]->value = $value;

        return $node;
    }

    private function shouldSkip(MethodCall $methodCall): bool
    {
        if (! $this->isName($methodCall->name, 'setId')) {
            return true;
        }

        $objectType = $this->getObjectType($methodCall);
        if (! $objectType instanceof ObjectType) {
            return true;
        }

        if (! $this->isDoctrineEntityClass($objectType->getClassName())) {
            return true;
        }

        if (! isset($methodCall->args[0])) {
            return true;
        }

        return $this->isUuidType($methodCall->args[0]->value);
    }

    private function getSetUuidMethodCallOnSameVariable(MethodCall $methodCall): ?MethodCall
    {
        $parentNode = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Expression) {
            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        }

        if ($parentNode === null) {
            return null;
        }

        $variableName = $this->getName($methodCall->var);

        /** @var ObjectType $variableType */
        $variableType = $this->getStaticType($methodCall->var);

        return $this->betterNodeFinder->findFirst($parentNode, function (Node $node) use (
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

            if (! $this->isName($node, 'setUuid')) {
                return false;
            }

            return true;
        });
    }

    private function isUuidType(Expr $expr): bool
    {
        $argumentStaticType = $this->getStaticType($expr);

        // UUID is already set
        if (! $argumentStaticType instanceof ObjectType) {
            return false;
        }

        return $argumentStaticType->getClassName() === DoctrineClass::RAMSEY_UUID;
    }
}
