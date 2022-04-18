<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Assign;

use RectorPrefix20220418\Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StringUtils;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/shopsys/shopsys/pull/524
 * @see \Rector\Tests\CodingStyle\Rector\Assign\PHPStormVarAnnotationRector\PHPStormVarAnnotationRectorTest
 */
final class PHPStormVarAnnotationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/YY5stJ/1
     */
    private const SINGLE_ASTERISK_COMMENT_START_REGEX = '#^\\/\\* #';
    /**
     * @var string
     * @see https://regex101.com/r/meD7rP/1
     */
    private const VAR_ANNOTATION_REGEX = '#\\@var(\\s)+\\$#';
    /**
     * @var string
     * @see https://regex101.com/r/yz2AZ7/1
     */
    private const VARIABLE_NAME_AND_TYPE_MATCH_REGEX = '#(?<variableName>\\$\\w+)(?<space>\\s+)(?<type>[\\\\\\w]+)#';
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change various @var annotation formats to one PHPStorm understands', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$config = 5;
/** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $config */
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/** @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $config */
$config = 5;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $expression = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        // unable to analyze
        if (!$expression instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $nextNode = $expression->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if (!$nextNode instanceof \PhpParser\Node) {
            return null;
        }
        $docContent = $this->getDocContent($nextNode);
        if ($docContent === '') {
            return null;
        }
        if (\strpos($docContent, '@var') === \false) {
            return null;
        }
        if (!$node->var instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        $varName = '$' . $this->getName($node->var);
        $varPattern = '# ' . \preg_quote($varName, '#') . ' #';
        if (!\Rector\Core\Util\StringUtils::isMatch($docContent, $varPattern)) {
            return null;
        }
        // switch docs
        $expression->setDocComment($this->createDocComment($nextNode));
        $expressionPhpDocInfo = $this->phpDocInfoFactory->createFromNode($expression);
        $expression->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, $expressionPhpDocInfo);
        // invoke override
        $expression->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        // remove otherwise empty node
        if ($nextNode instanceof \PhpParser\Node\Stmt\Nop) {
            $this->removeNode($nextNode);
            return null;
        }
        // remove commnets
        $nextNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, null);
        $nextNode->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS, null);
        return $node;
    }
    private function getDocContent(\PhpParser\Node $node) : string
    {
        $docComment = $node->getDocComment();
        if ($docComment !== null) {
            return $docComment->getText();
        }
        if ($node->getComments() !== []) {
            $docContent = '';
            foreach ($node->getComments() as $comment) {
                $docContent .= $comment->getText();
            }
            return $docContent;
        }
        return '';
    }
    private function createDocComment(\PhpParser\Node $node) : \PhpParser\Comment\Doc
    {
        if ($node->getDocComment() !== null) {
            return $node->getDocComment();
        }
        $docContent = $this->getDocContent($node);
        // normalize content
        // starts with "/*", instead of "/**"
        if (\strncmp($docContent, '/* ', \strlen('/* ')) === 0) {
            $docContent = \RectorPrefix20220418\Nette\Utils\Strings::replace($docContent, self::SINGLE_ASTERISK_COMMENT_START_REGEX, '/** ');
        }
        // $value is first, instead of type is first
        if (\Rector\Core\Util\StringUtils::isMatch($docContent, self::VAR_ANNOTATION_REGEX)) {
            $docContent = \RectorPrefix20220418\Nette\Utils\Strings::replace($docContent, self::VARIABLE_NAME_AND_TYPE_MATCH_REGEX, '$3$2$1');
        }
        return new \PhpParser\Comment\Doc($docContent);
    }
}
