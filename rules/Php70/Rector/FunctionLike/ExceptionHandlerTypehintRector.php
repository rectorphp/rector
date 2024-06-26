<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Rector\AbstractRector;
use Rector\Util\StringUtils;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php70\Rector\FunctionLike\ExceptionHandlerTypehintRector\ExceptionHandlerTypehintRectorTest
 */
final class ExceptionHandlerTypehintRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var string
     * @see https://regex101.com/r/VBFXCR/1
     */
    private const HANDLE_INSENSITIVE_REGEX = '#handle#i';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change typehint from `Exception` to `Throwable`.', [new CodeSample(<<<'CODE_SAMPLE'
function handler(Exception $exception) { ... }
set_exception_handler('handler');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function handler(Throwable $exception) { ... }
set_exception_handler('handler');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Function_::class, ClassMethod::class];
    }
    /**
     * @param Function_|ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        // exception handle has 1 param exactly
        if (\count($node->params) !== 1) {
            return null;
        }
        $paramNode = $node->params[0];
        if ($paramNode->type === null) {
            return null;
        }
        // handle only Exception typehint
        $actualType = $paramNode->type instanceof NullableType ? $this->getName($paramNode->type->type) : $this->getName($paramNode->type);
        if ($actualType !== 'Exception') {
            return null;
        }
        // is probably handling exceptions
        if (!StringUtils::isMatch((string) $node->name, self::HANDLE_INSENSITIVE_REGEX)) {
            return null;
        }
        if (!$paramNode->type instanceof NullableType) {
            $paramNode->type = new FullyQualified('Throwable');
        } else {
            $paramNode->type->type = new FullyQualified('Throwable');
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::THROWABLE_TYPE;
    }
}
