<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpAttribute\AnnotationToAttributeConverter;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://wiki.php.net/rfc/attributes_v2
 * @see https://wiki.php.net/rfc/shorter_attribute_syntax
 * @see https://wiki.php.net/rfc/shorter_attribute_syntax_change - FINAL #[...] syntax !
 *
 * @see \Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\AnnotationToAttributeRectorTest
 */
final class AnnotationToAttributeRector extends AbstractRector
{
    /**
     * @var AnnotationToAttributeConverter
     */
    private $annotationToAttributeConverter;

    public function __construct(AnnotationToAttributeConverter $annotationToAttributeConverter)
    {
        $this->annotationToAttributeConverter = $annotationToAttributeConverter;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change annotation to attribute', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Doctrine\ORM\Attributes as ORM;

/**
  * @ORM\Entity
  */
class SomeClass
{
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
use Doctrine\ORM\Attributes as ORM;

#[ORM\Entity]
class SomeClass
{
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [
            Class_::class,
            Property::class,
            ClassMethod::class,
            Function_::class,
            Closure::class,
            ArrowFunction::class,
        ];
    }

    /**
     * @param Class_|Property|ClassMethod|Function_|Closure|ArrowFunction $node
     */
    public function refactor(Node $node): ?Node
    {
        return $this->annotationToAttributeConverter->convertNode($node);
    }
}
