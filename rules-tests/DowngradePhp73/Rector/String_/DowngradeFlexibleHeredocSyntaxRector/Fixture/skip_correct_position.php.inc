<?php

use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class SomeRuleDefinition
{

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change visibility of property from parent class.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
any content
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
another content
CODE_SAMPLE
                ),
            ]
        );
    }
}
