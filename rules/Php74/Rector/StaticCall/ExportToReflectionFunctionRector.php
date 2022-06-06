<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/deprecations_php_7_4 (not confirmed yet)
 * @changelog https://3v4l.org/RTCUq
 * @see \Rector\Tests\Php74\Rector\StaticCall\ExportToReflectionFunctionRector\ExportToReflectionFunctionRectorTest
 */
final class ExportToReflectionFunctionRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(\Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
    {
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::EXPORT_TO_REFLECTION_FUNCTION;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change export() to ReflectionFunction alternatives', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$reflectionFunction = ReflectionFunction::export('foo');
$reflectionFunctionAsString = ReflectionFunction::export('foo', true);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$reflectionFunction = new ReflectionFunction('foo');
$reflectionFunctionAsString = (string) new ReflectionFunction('foo');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->class instanceof \PhpParser\Node\Name) {
            return null;
        }
        $callerType = $this->nodeTypeResolver->getType($node->class);
        if (!$callerType->isSuperTypeOf(new \PHPStan\Type\ObjectType('ReflectionFunction'))->yes()) {
            return null;
        }
        if (!$this->isName($node->name, 'export')) {
            return null;
        }
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($node->args, 0)) {
            return null;
        }
        /** @var Arg $firstArg */
        $firstArg = $node->args[0];
        $new = new \PhpParser\Node\Expr\New_($node->class, [new \PhpParser\Node\Arg($firstArg->value)]);
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($node->args, 1)) {
            return $new;
        }
        /** @var Arg $secondArg */
        $secondArg = $node->args[1];
        if ($this->valueResolver->isTrue($secondArg->value)) {
            return new \PhpParser\Node\Expr\Cast\String_($new);
        }
        return $new;
    }
}
