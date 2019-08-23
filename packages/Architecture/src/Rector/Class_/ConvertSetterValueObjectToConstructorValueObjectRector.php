<?php declare(strict_types=1);

namespace Rector\Architecture\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Architecture\Tests\Rector\Class_\ConvertSetterValueObjectToConstructorValueObjectRector\ConvertSetterValueObjectToConstructorValueObjectRectorTest
 */
final class ConvertSetterValueObjectToConstructorValueObjectRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Convert setter-based value objects of specific type to constructor arguments', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeValueObject
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $age;

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function setAge(int $age)
    {
        $this->age = age;
    }
}

class SomeClass
{
    public function run()
    {
        $someValueObject = new SomeValueObject();
        $someValueObject->setName('Tom');
        $someValueObject->setAge(50);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeValueObject
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $age;

    public function __construct(string $name, int $age)
    {
        $this->name = $name;
        $this->age = age;
    }
}

class SomeClass
{
    public function run()
    {
        $someValueObject = new SomeValueObject('Tom', 50);
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
        return [Class_::class, New_::class, MethodCall::class];
    }

    /**
     * @param Class_|New_|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        // change the node

        return $node;
    }
}
