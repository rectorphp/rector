<?php

declare (strict_types=1);
namespace Rector\Php84\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php84\Rector\FuncCall\AddEscapeArgumentRector\AddEscapeArgumentRectorTest
 */
final class AddEscapeArgumentRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add escape argument on CSV function calls', [new CodeSample(<<<'CODE_SAMPLE'
str_getcsv($string, separator: ',', enclosure: '"');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
str_getcsv($string, separator: ',', enclosure: '"', escape: '\\');
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [FuncCall::class, MethodCall::class];
    }
    /**
     * @param FuncCall|MethodCall $node
     * @return null|\PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall
     */
    public function refactor(Node $node)
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if ($node instanceof FuncCall) {
            if (!$this->isNames($node, ['fputcsv', 'fgetcsv', 'str_getcsv'])) {
                return null;
            }
            if ($this->shouldSkipNamedArg($node)) {
                return null;
            }
            $name = $this->getName($node);
            if (\in_array($name, ['fputcsv', 'fgetcsv'], \true) && isset($node->getArgs()[4])) {
                return null;
            }
            if ($name === 'str_getcsv' && isset($node->getArgs()[3])) {
                return null;
            }
            $node->args[\count($node->getArgs())] = new Arg(new String_('\\'), \false, \false, [], new Identifier('escape'));
            return $node;
        }
        if (!$this->isObjectType($node->var, new ObjectType('SplFileObject'))) {
            return null;
        }
        $name = $this->getName($node->name);
        if (!\in_array($name, ['setCsvControl', 'fputcsv', 'fgetcsv'], \true)) {
            return null;
        }
        if ($this->shouldSkipNamedArg($node)) {
            return null;
        }
        if (\in_array($name, ['setCsvControl', 'fgetcsv'], \true) && isset($node->getArgs()[2])) {
            return null;
        }
        if ($name === 'fputcsv' && isset($node->getArgs()[3])) {
            return null;
        }
        $node->args[\count($node->getArgs())] = new Arg(new String_('\\'), \false, \false, [], new Identifier('escape'));
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::REQUIRED_ESCAPE_PARAMETER;
    }
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall $node
     */
    private function shouldSkipNamedArg($node) : bool
    {
        foreach ($node->getArgs() as $arg) {
            // already defined in named arg
            if ($arg->name instanceof Identifier && $arg->name->toString() === 'escape') {
                return \true;
            }
        }
        return \false;
    }
}
