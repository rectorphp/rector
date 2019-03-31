<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\Application\ConstantNodeCollector;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Php\Regex\RegexPatternArgumentManipulator;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/dRG8U
 */
final class RegexDashEscapeRector extends AbstractRector
{
    /**
     * @var string
     */
    private const LEFT_HAND_UNESCAPED_DASH_PATTERN = '#(\\\\(w|s|d))-(?!\])#i';

    /**
     * @var string
     * @see https://regex101.com/r/TBVme9/1
     */
    private const RIGHT_HAND_UNESCAPED_DASH_PATTERN = '#(?<!\[)-\\\\(w|s|d)#i';

    /**
     * @var ConstantNodeCollector
     */
    private $constantNodeCollector;

    /**
     * @var RegexPatternArgumentManipulator
     */
    private $regexPatternArgumentManipulator;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        ConstantNodeCollector $constantNodeCollector,
        RegexPatternArgumentManipulator $regexPatternArgumentManipulator
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->constantNodeCollector = $constantNodeCollector;
        $this->regexPatternArgumentManipulator = $regexPatternArgumentManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Escape - in some cases', [
            new CodeSample(
                <<<'CODE_SAMPLE'
preg_match("#[\w-()]#", 'some text');
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
preg_match("#[\w\-()]#", 'some text');
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, StaticCall::class];
    }

    /**
     * @param FuncCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $regexArgument = $this->regexPatternArgumentManipulator->matchCallArgumentWithRegexPattern($node);
        if ($regexArgument === null) {
            return null;
        }

        if (! $this->isStringyType($regexArgument)) {
            return null;
        }

        $this->escapeDashInPattern($regexArgument);

        return $node;
    }

    private function escapeDashInPattern(Expr $expr): void
    {
        if ($expr instanceof ClassConstFetch) {
            $this->processClassConstFetch($expr);
        }

        // pattern can be defined in property or contant above
        if ($expr instanceof Variable) {
            $this->processVariable($expr);
        }

        if ($expr instanceof String_) {
            $this->escapeStringNode($expr);
        }
    }

    private function processClassConstFetch(ClassConstFetch $classConstFetch): void
    {
        $className = $classConstFetch->getAttribute(Attribute::CLASS_NAME);
        if (! is_string($className)) {
            return;
        }

        $constantName = $this->getName($classConstFetch->name);
        if ($constantName === null) {
            return;
        }

        $classConstNode = $this->constantNodeCollector->findConstant($constantName, $className);
        if ($classConstNode !== null) {
            if ($classConstNode->consts[0]->value instanceof String_) {
                /** @var String_ $stringNode */
                $stringNode = $classConstNode->consts[0]->value;
                $this->escapeStringNode($stringNode);
            }
        }
    }

    private function processVariable(Expr $expr): void
    {
        $assignNodes = $this->findAssigners($expr);

        foreach ($assignNodes as $assignNode) {
            if ($assignNode->expr instanceof String_) {
                $this->escapeStringNode($assignNode->expr);
            }
        }
    }

    private function escapeStringNode(String_ $stringNode): void
    {
        $stringValue = $stringNode->value;

        if (Strings::match($stringValue, self::LEFT_HAND_UNESCAPED_DASH_PATTERN)) {
            $stringNode->value = Strings::replace($stringValue, self::LEFT_HAND_UNESCAPED_DASH_PATTERN, '$1\-');
            // helped needed to skip re-escaping regular expression
            $stringNode->setAttribute('is_regular_pattern', true);
            return;
        }

        if (Strings::match($stringValue, self::RIGHT_HAND_UNESCAPED_DASH_PATTERN)) {
            $stringNode->value = Strings::replace($stringValue, self::RIGHT_HAND_UNESCAPED_DASH_PATTERN, '\-$2');
            // helped needed to skip re-escaping regular expression
            $stringNode->setAttribute('is_regular_pattern', true);
        }
    }

    /**
     * @return Assign[]
     */
    private function findAssigners(Node $variableNode): array
    {
        $methodNode = $variableNode->getAttribute(Attribute::METHOD_NODE);
        if ($methodNode === null) {
            return [];
        }

        /** @var Assign[] $assignNode */
        return $this->betterNodeFinder->find([$methodNode], function (Node $node) use ($variableNode) {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $this->areNodesEqual($node->var, $variableNode)) {
                return null;
            }

            return $node;
        });
    }
}
