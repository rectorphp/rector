<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\Php\PhpVersionProvider;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\ValueObject\AddParamTypeForFunctionLikeWithinCallLikeArgDeclaration;
use Rector\ValueObject\PhpVersionFeature;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202506\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\AddParamTypeForFunctionLikeWithinCallLikeArgDeclarationRector\AddParamTypeForFunctionLikeWithinCallLikeArgDeclarationRectorTest
 */
final class AddParamTypeForFunctionLikeWithinCallLikeArgDeclarationRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     */
    private TypeComparator $typeComparator;
    /**
     * @readonly
     */
    private PhpVersionProvider $phpVersionProvider;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @var AddParamTypeForFunctionLikeWithinCallLikeArgDeclaration[]
     */
    private array $addParamTypeForFunctionLikeParamDeclarations = [];
    private bool $hasChanged = \false;
    public function __construct(TypeComparator $typeComparator, PhpVersionProvider $phpVersionProvider, StaticTypeMapper $staticTypeMapper)
    {
        $this->typeComparator = $typeComparator;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add param types where needed', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
(new SomeClass)->process(function ($parameter) {});
CODE_SAMPLE
, <<<'CODE_SAMPLE'
(new SomeClass)->process(function (string $parameter) {});
CODE_SAMPLE
, [new AddParamTypeForFunctionLikeWithinCallLikeArgDeclaration('SomeClass', 'process', 0, 0, new StringType())])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param CallLike $node
     */
    public function refactor(Node $node) : ?Node
    {
        $this->hasChanged = \false;
        foreach ($this->addParamTypeForFunctionLikeParamDeclarations as $addParamTypeForFunctionLikeParamDeclaration) {
            switch (\true) {
                case $node instanceof MethodCall:
                    $type = $node->var;
                    break;
                case $node instanceof StaticCall:
                    $type = $node->class;
                    break;
                default:
                    $type = null;
                    break;
            }
            if (!$type instanceof Node) {
                continue;
            }
            if (!$this->isObjectType($type, $addParamTypeForFunctionLikeParamDeclaration->getObjectType())) {
                continue;
            }
            if (!($node->name ?? null) instanceof Identifier) {
                continue;
            }
            if (!$this->isName($node->name, $addParamTypeForFunctionLikeParamDeclaration->getMethodName())) {
                continue;
            }
            $this->processFunctionLike($node, $addParamTypeForFunctionLikeParamDeclaration);
        }
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, AddParamTypeForFunctionLikeWithinCallLikeArgDeclaration::class);
        $this->addParamTypeForFunctionLikeParamDeclarations = $configuration;
    }
    private function processFunctionLike(CallLike $callLike, AddParamTypeForFunctionLikeWithinCallLikeArgDeclaration $addParamTypeForFunctionLikeWithinCallLikeArgDeclaration) : void
    {
        if ($callLike->isFirstClassCallable()) {
            return;
        }
        if (\is_int($addParamTypeForFunctionLikeWithinCallLikeArgDeclaration->getCallLikePosition())) {
            if ($callLike->getArgs() === []) {
                return;
            }
            $arg = $callLike->args[$addParamTypeForFunctionLikeWithinCallLikeArgDeclaration->getCallLikePosition()] ?? null;
            if (!$arg instanceof Arg) {
                return;
            }
            // int positions shouldn't have names
            if ($arg->name instanceof Identifier) {
                return;
            }
        } else {
            $args = \array_filter($callLike->getArgs(), static function (Arg $arg) use($addParamTypeForFunctionLikeWithinCallLikeArgDeclaration) : bool {
                if (!$arg->name instanceof Identifier) {
                    return \false;
                }
                return $arg->name->name === $addParamTypeForFunctionLikeWithinCallLikeArgDeclaration->getCallLikePosition();
            });
            if ($args === []) {
                return;
            }
            $arg = \array_values($args)[0];
        }
        $functionLike = $arg->value;
        if (!$functionLike instanceof FunctionLike) {
            return;
        }
        if (!isset($functionLike->params[$addParamTypeForFunctionLikeWithinCallLikeArgDeclaration->getFunctionLikePosition()])) {
            return;
        }
        $this->refactorParameter($functionLike->params[$addParamTypeForFunctionLikeWithinCallLikeArgDeclaration->getFunctionLikePosition()], $addParamTypeForFunctionLikeWithinCallLikeArgDeclaration);
    }
    private function refactorParameter(Param $param, AddParamTypeForFunctionLikeWithinCallLikeArgDeclaration $addParamTypeForFunctionLikeWithinCallLikeArgDeclaration) : void
    {
        $newParameterType = $addParamTypeForFunctionLikeWithinCallLikeArgDeclaration->getParamType();
        // already set → no change
        if ($param->type instanceof Node) {
            $currentParamType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
            if ($this->typeComparator->areTypesEqual($currentParamType, $newParameterType)) {
                return;
            }
        }
        $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($newParameterType, TypeKind::PARAM);
        $this->hasChanged = \true;
        // remove it
        if ($newParameterType instanceof MixedType) {
            if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::MIXED_TYPE)) {
                $param->type = $paramTypeNode;
                return;
            }
            $param->type = null;
            return;
        }
        $param->type = $paramTypeNode;
    }
}
