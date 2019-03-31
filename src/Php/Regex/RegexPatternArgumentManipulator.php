<?php declare(strict_types=1);

namespace Rector\Php\Regex;

use Nette\Utils\Strings;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\Application\ConstantNodeCollector;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class RegexPatternArgumentManipulator
{
    /**
     * @var int[]
     */
    private $functionsWithPatternsToArgumentPosition = [
        'preg_match' => 0,
        'preg_replace_callback_array' => 0,
        'preg_replace_callback' => 0,
        'preg_replace' => 0,
        'preg_match_all' => 0,
        'preg_split' => 0,
        'preg_grep' => 0,
    ];

    /**
     * @var int[][]
     */
    private $staticMethodsWithPatternsToArgumentPosition = [
        Strings::class => [
            'match' => 1,
            'matchAll' => 1,
            'replace' => 1,
            'split' => 1,
        ],
    ];

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ConstantNodeCollector
     */
    private $constantNodeCollector;

    public function __construct(
        NodeTypeResolver $nodeTypeResolver,
        NameResolver $nameResolver,
        ConstantNodeCollector $constantNodeCollector
    ) {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nameResolver = $nameResolver;
        $this->constantNodeCollector = $constantNodeCollector;
    }

    public function matchCallArgumentWithRegexPattern(Expr $expr): ?Expr
    {
        if ($expr instanceof FuncCall) {
            return $this->processFuncCall($expr);
        }

        if ($expr instanceof StaticCall) {
            return $this->processStaticCall($expr);
        }

        return null;
    }

    private function processFuncCall(FuncCall $funcCall): ?String_
    {
        foreach ($this->functionsWithPatternsToArgumentPosition as $functionName => $argumentPosition) {
            if (! $this->nameResolver->isName($funcCall, $functionName)) {
                continue;
            }

            if (! isset($funcCall->args[$argumentPosition])) {
                return null;
            }

            return $this->resolveArgumentValue($funcCall->args[$argumentPosition]->value);
        }

        return null;
    }

    private function processStaticCall(StaticCall $staticCall): ?Expr
    {
        foreach ($this->staticMethodsWithPatternsToArgumentPosition as $type => $methodNamesToArgumentPosition) {
            if (! $this->nodeTypeResolver->isType($staticCall, $type)) {
                continue;
            }

            foreach ($methodNamesToArgumentPosition as $methodName => $argumentPosition) {
                if (! $this->nameResolver->isName($staticCall, $methodName)) {
                    continue;
                }

                if (! isset($staticCall->args[$argumentPosition])) {
                    return null;
                }

                return $staticCall->args[$argumentPosition]->value;
            }
        }

        return null;
    }

    private function resolveArgumentValue(Expr $expr): ?String_
    {
        if ($expr instanceof String_) {
            return $expr;
        }

        if ($expr instanceof Expr\ClassConstFetch) {
            $className = $expr->getAttribute(Attribute::CLASS_NAME);
            if (! is_string($className)) {
                return null;
            }

            $constantName = $this->nameResolver->resolve($expr->name);

            if ($constantName === null) {
                return null;
            }

            $classConstNode = $this->constantNodeCollector->findConstant($constantName, $className);

            if ($classConstNode === null) {
                return null;
            }

            if ($classConstNode->consts[0]->value instanceof String_) {
                /** @var String_ $stringNode */
                return $classConstNode->consts[0]->value;
            }
        }

        return null;
    }
}
