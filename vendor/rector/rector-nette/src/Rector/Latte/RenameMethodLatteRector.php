<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\Latte;

use Rector\Nette\Contract\Rector\LatteRectorInterface;
use Rector\Nette\Latte\Parser\TemplateTypeParser;
use Rector\Nette\Latte\Parser\VarTypeParser;
use Rector\Renaming\Collector\MethodCallRenameCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Nette\Tests\Rector\Latte\RenameMethodLatteRector\RenameMethodLatteRectorTest
 */
final class RenameMethodLatteRector implements \Rector\Nette\Contract\Rector\LatteRectorInterface
{
    /**
     * @var \Rector\Renaming\Collector\MethodCallRenameCollector
     */
    private $methodCallRenameCollector;
    /**
     * @var \Rector\Nette\Latte\Parser\TemplateTypeParser
     */
    private $templateTypeParser;
    /**
     * @var \Rector\Nette\Latte\Parser\VarTypeParser
     */
    private $varTypeParser;
    public function __construct(\Rector\Renaming\Collector\MethodCallRenameCollector $methodCallRenameCollector, \Rector\Nette\Latte\Parser\TemplateTypeParser $templateTypeParser, \Rector\Nette\Latte\Parser\VarTypeParser $varTypeParser)
    {
        $this->methodCallRenameCollector = $methodCallRenameCollector;
        $this->templateTypeParser = $templateTypeParser;
        $this->varTypeParser = $varTypeParser;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Renames method calls in LATTE templates', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
{varType SomeClass $someClass}

<div n:foreach="$someClass->oldCall() as $item"></div>
CODE_SAMPLE
, <<<'CODE_SAMPLE'
{varType SomeClass $someClass}

<div n:foreach="$someClass->newCall() as $item"></div>
CODE_SAMPLE
)]);
    }
    public function changeContent(string $content) : string
    {
        $typesToVariables = $this->findTypesForVariables($content);
        foreach ($this->methodCallRenameCollector->getMethodCallRenames() as $methodCallRename) {
            $oldObjectType = $methodCallRename->getOldObjectType();
            $className = $oldObjectType->getClassName();
            if (!isset($typesToVariables[$className])) {
                continue;
            }
            foreach ($typesToVariables[$className] as $variableName) {
                $content = \str_replace('$' . $variableName . '->' . $methodCallRename->getOldMethod() . '(', '$' . $variableName . '->' . $methodCallRename->getNewMethod() . '(', $content);
            }
        }
        return $content;
    }
    /**
     * @return array<string, string[]> list of types with all variables of this type
     */
    private function findTypesForVariables(string $content) : array
    {
        $typesToVariables = [];
        $latteVariableTypes = \array_merge($this->templateTypeParser->parse($content), $this->varTypeParser->parse($content));
        foreach ($latteVariableTypes as $latteVariableType) {
            if (!isset($typesToVariables[$latteVariableType->getType()])) {
                $typesToVariables[$latteVariableType->getType()] = [];
            }
            $typesToVariables[$latteVariableType->getType()][] = $latteVariableType->getName();
        }
        return $typesToVariables;
    }
}
