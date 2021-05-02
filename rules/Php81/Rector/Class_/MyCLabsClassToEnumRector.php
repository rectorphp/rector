<?php

declare(strict_types=1);

namespace Rector\Php81\Rector\Class_;

use MyCLabs\Enum\Enum;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // change the node
        if (! $this->isObjectType($node, new ObjectType('MyCLabs\Enum\Enum'))) {
            return null;
        }

        $shortClassName = $this->nodeNameResolver->getShortName($node);
        $enum = new Enum_($shortClassName);

        // constant to cases
        foreach ($node->getConstants() as $classConst) {
            $enum->stmts[] = $this->createEnumCase($classConst);
        }

        return $enum;
    }

    private function createEnumCase(ClassConst $classConst): EnumCase
    {
        $constConst = $classConst->consts[0];
        $enumCase = new EnumCase($constConst->name, $constConst->value);

        $this->mirrorComments($enumCase, $classConst);

        return $enumCase;
    }
}
