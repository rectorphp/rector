<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\PropertyFetch;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Scalar;
use Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use RectorPrefix20220209\Symplify\PackageBuilder\Php\TypeChecker;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220209\Webmozart\Assert\Assert;
/**
 * @see https://github.com/laravel/laravel/pull/5670
 * @see https://github.com/laravel/framework/pull/38868
 * @see https://wiki.php.net/rfc/nullsafe_operator
 *
 * @see \Rector\Laravel\Tests\Rector\PropertyFetch\OptionalToNullsafeOperatorRector\OptionalToNullsafeOperatorRectorTest
 */
final class OptionalToNullsafeOperatorRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface, \Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const EXCLUDE_METHODS = 'exclude_methods';
    /**
     * @var array<class-string<Expr>>
     */
    private const SKIP_VALUE_TYPES = [\PhpParser\Node\Expr\ConstFetch::class, \PhpParser\Node\Scalar::class, \PhpParser\Node\Expr\Array_::class, \PhpParser\Node\Expr\ClassConstFetch::class];
    /**
     * @var string[]
     */
    private $excludeMethods = [];
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Php\TypeChecker
     */
    private $typeChecker;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(\RectorPrefix20220209\Symplify\PackageBuilder\Php\TypeChecker $typeChecker, \Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
    {
        $this->typeChecker = $typeChecker;
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert simple calls to optional helper to use the nullsafe operator', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
optional($user)->getKey();
optional($user)->id;
// macro methods
optional($user)->present()->getKey();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$user?->getKey();
$user?->id;
// macro methods
optional($user)->present()->getKey();
CODE_SAMPLE
, [self::EXCLUDE_METHODS => ['present']])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\PropertyFetch::class, \PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall|PropertyFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->var instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        if (!$this->isName($node->var->name, 'optional')) {
            return null;
        }
        // exclude macro methods
        if ($node instanceof \PhpParser\Node\Expr\MethodCall && $this->isNames($node->name, $this->excludeMethods)) {
            return null;
        }
        if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($node->var->args, 0)) {
            return null;
        }
        // skip if the second arg exists and not null
        if ($this->hasCallback($node->var)) {
            return null;
        }
        /** @var Arg $firstArg */
        $firstArg = $node->var->args[0];
        // skip if the first arg cannot be used as variable directly
        if ($this->typeChecker->isInstanceOf($firstArg->value, self::SKIP_VALUE_TYPES)) {
            return null;
        }
        if ($node instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return new \PhpParser\Node\Expr\NullsafePropertyFetch($firstArg->value, $node->name);
        }
        return new \PhpParser\Node\Expr\NullsafeMethodCall($firstArg->value, $node->name, $node->args);
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersion::PHP_80;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $excludeMethods = $configuration[self::EXCLUDE_METHODS] ?? $configuration;
        \RectorPrefix20220209\Webmozart\Assert\Assert::isArray($excludeMethods);
        \RectorPrefix20220209\Webmozart\Assert\Assert::allString($excludeMethods);
        $this->excludeMethods = $excludeMethods;
    }
    private function hasCallback(\PhpParser\Node\Expr\FuncCall $funcCall) : bool
    {
        return isset($funcCall->args[1]) && $funcCall->args[1] instanceof \PhpParser\Node\Arg && !$this->valueResolver->isNull($funcCall->args[1]->value);
    }
}
