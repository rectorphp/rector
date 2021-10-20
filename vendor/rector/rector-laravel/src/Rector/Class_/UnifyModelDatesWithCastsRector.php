<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/laravel/framework/pull/32856
 *
 * @see \Rector\Laravel\Tests\Rector\Class_\UnifyModelDatesWithCastsRector\UnifyModelDatesWithCastsRectorTest
 */
final class UnifyModelDatesWithCastsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    public function __construct(\Rector\Core\NodeManipulator\ClassInsertManipulator $classInsertManipulator)
    {
        $this->classInsertManipulator = $classInsertManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Unify Model $dates property with $casts', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $casts = [
        'age' => 'integer',
    ];

    protected $dates = ['birthday'];
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $casts = [
        'age' => 'integer', 'birthday' => 'datetime',
    ];
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node, new \PHPStan\Type\ObjectType('Illuminate\\Database\\Eloquent\\Model'))) {
            return null;
        }
        $datesProperty = $node->getProperty('dates');
        if (!$datesProperty instanceof \PhpParser\Node\Stmt\Property) {
            return null;
        }
        $datesPropertyProperty = $datesProperty->props[0];
        if (!$datesPropertyProperty->default instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $dates = $this->valueResolver->getValue($datesPropertyProperty->default);
        if ($dates === []) {
            return null;
        }
        $castsProperty = $node->getProperty('casts');
        // add property $casts if not exists
        if ($castsProperty === null) {
            $castsProperty = $this->createCastsProperty();
            $this->classInsertManipulator->addAsFirstMethod($node, $castsProperty);
        }
        $castsPropertyProperty = $castsProperty->props[0];
        if (!$castsPropertyProperty->default instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $casts = $this->valueResolver->getValue($castsPropertyProperty->default);
        // exclude attributes added in $casts
        $missingDates = \array_diff($dates, \array_keys($casts));
        foreach ($missingDates as $missingDate) {
            $castsPropertyProperty->default->items[] = new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Scalar\String_('datetime'), new \PhpParser\Node\Scalar\String_($missingDate));
        }
        $this->nodeRemover->removeNode($datesProperty);
        return null;
    }
    private function createCastsProperty() : \PhpParser\Node\Stmt\Property
    {
        $propertyBuilder = new \RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder('casts');
        $propertyBuilder->makeProtected();
        $propertyBuilder->setDefault([]);
        $property = $propertyBuilder->getNode();
        $this->phpDocInfoFactory->createFromNode($property);
        return $property;
    }
}
