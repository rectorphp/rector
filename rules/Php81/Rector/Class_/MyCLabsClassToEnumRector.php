<?php

declare(strict_types=1);

namespace Rector\Php81\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Php81\NodeFactory\EnumFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/enumerations
 * @changelog https://github.com/myclabs/php-enum
 *
 * @see \Rector\Tests\Php81\Rector\Class_\MyCLabsClassToEnumRector\MyCLabsClassToEnumRectorTest
 */
final class MyCLabsClassToEnumRector extends AbstractRector
{
    /**
     * @var EnumFactory
     */
    private $enumFactory;

    public function __construct(EnumFactory $enumFactory)
    {
        $this->enumFactory = $enumFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Refactor MyCLabs enum class to native Enum', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use MyCLabs\Enum\Enum;

final class Action extends Enum
{
    private const VIEW = 'view';
    private const EDIT = 'edit';
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
enum Action
{
    case VIEW = 'view';
    case EDIT = 'edit';
}
CODE_SAMPLE

            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, StaticCall::class];
    }

    /**
     * @param Class_|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Class_) {
            // change the node
            if (! $this->isObjectType($node, new ObjectType('MyCLabs\Enum\Enum'))) {
                return null;
            }

            return $this->enumFactory->createFromClass($node);
        }

        if ($this->isObjectType($node->class, new ObjectType('MyCLabs\Enum\Enum'))) {
            $className = $this->getName($node->class);
            if ($className === null) {
                return null;
            }

            $methodName = $this->getName($node->name);
            if ($methodName === null) {
                return null;
            }

            return new ClassConstFetch(new FullyQualified($className), new Identifier($methodName));
        }

        return null;
    }
}
