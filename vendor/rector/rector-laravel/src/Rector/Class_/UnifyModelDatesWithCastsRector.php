<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Laravel\Rector\Class_;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\NodeManipulator\ClassInsertManipulator;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220606\Webmozart\Assert\Assert;
/**
 * @see https://github.com/laravel/framework/pull/32856
 *
 * @see \Rector\Laravel\Tests\Rector\Class_\UnifyModelDatesWithCastsRector\UnifyModelDatesWithCastsRectorTest
 */
final class UnifyModelDatesWithCastsRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    public function __construct(ClassInsertManipulator $classInsertManipulator)
    {
        $this->classInsertManipulator = $classInsertManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Unify Model $dates property with $casts', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node, new ObjectType('Illuminate\\Database\\Eloquent\\Model'))) {
            return null;
        }
        $datesProperty = $node->getProperty('dates');
        if (!$datesProperty instanceof Property) {
            return null;
        }
        $datesPropertyProperty = $datesProperty->props[0];
        if (!$datesPropertyProperty->default instanceof Array_) {
            return null;
        }
        $dates = $this->valueResolver->getValue($datesPropertyProperty->default);
        if (!\is_array($dates)) {
            return null;
        }
        if ($dates === []) {
            return null;
        }
        $castsProperty = $node->getProperty('casts');
        // add property $casts if not exists
        if (!$castsProperty instanceof Property) {
            $castsProperty = $this->createCastsProperty();
            $this->classInsertManipulator->addAsFirstMethod($node, $castsProperty);
        }
        $castsPropertyProperty = $castsProperty->props[0];
        if (!$castsPropertyProperty->default instanceof Array_) {
            return null;
        }
        $casts = $this->valueResolver->getValue($castsPropertyProperty->default);
        // exclude attributes added in $casts
        $missingDates = \array_diff($dates, \array_keys($casts));
        Assert::allString($missingDates);
        foreach ($missingDates as $missingDate) {
            $castsPropertyProperty->default->items[] = new ArrayItem(new String_('datetime'), new String_($missingDate));
        }
        $this->nodeRemover->removeNode($datesProperty);
        return null;
    }
    private function createCastsProperty() : Property
    {
        $propertyBuilder = new PropertyBuilder('casts');
        $propertyBuilder->makeProtected();
        $propertyBuilder->setDefault([]);
        $property = $propertyBuilder->getNode();
        $this->phpDocInfoFactory->createFromNode($property);
        return $property;
    }
}
