<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/enumerations
 * @changelog https://github.com/myclabs/php-enum
 *
 * @see \Rector\Tests\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector\MyCLabsMethodCallToEnumConstRectorTest
 */
final class MyCLabsMethodCallToEnumConstRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor MyCLabs enum fetch to Enum const', [new CodeSample(<<<'CODE_SAMPLE'
$name = SomeEnum::VALUE()->getKey();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$name = SomeEnum::VALUE;
CODE_SAMPLE
)]);
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
        if (!$this->isObjectType($node->var, new ObjectType('MyCLabs\\Enum\\Enum'))) {
            return null;
        }
        if (!$this->isName($node->name, 'getKey')) {
            return null;
        }
        if (!$node->var instanceof StaticCall) {
            return null;
        }
        $staticCall = $node->var;
        $className = $this->getName($staticCall->class);
        if ($className === null) {
            return null;
        }
        $enumCaseName = $this->getName($staticCall->name);
        if ($enumCaseName === null) {
            return null;
        }
        return $this->nodeFactory->createClassConstFetch($className, $enumCaseName);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ENUM;
    }
}
