<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Form;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;

/**
 * Converts all:
 * - getParent() {
 *      return 'collection';
 * }
 * - getExtendedType() {
 *      return 'collection';
 * }
 *
 * into:
 * - getParent() {
 *      return CollectionType::class;
 * }
 * - getExtendedType() {
 *      return CollectionType::class;
 * }
 */
final class FormTypeGetParentRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $nameToClassMap = [
        'form' => 'Symfony\Component\Form\Extension\Core\Type\FormType',
        'birthday' => 'Symfony\Component\Form\Extension\Core\Type\BirthdayType',
        'checkbox' => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
        'collection' => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
        'country' => 'Symfony\Component\Form\Extension\Core\Type\CountryType',
        'currency' => 'Symfony\Component\Form\Extension\Core\Type\CurrencyType',
        'date' => 'Symfony\Component\Form\Extension\Core\Type\DateType',
        'datetime' => 'Symfony\Component\Form\Extension\Core\Type\DatetimeType',
        'email' => 'Symfony\Component\Form\Extension\Core\Type\EmailType',
        'file' => 'Symfony\Component\Form\Extension\Core\Type\FileType',
        'hidden' => 'Symfony\Component\Form\Extension\Core\Type\HiddenType',
        'integer' => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
        'language' => 'Symfony\Component\Form\Extension\Core\Type\LanguageType',
        'locale' => 'Symfony\Component\Form\Extension\Core\Type\LocaleType',
        'money' => 'Symfony\Component\Form\Extension\Core\Type\MoneyType',
        'number' => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
        'password' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
        'percent' => 'Symfony\Component\Form\Extension\Core\Type\PercentType',
        'radio' => 'Symfony\Component\Form\Extension\Core\Type\RadioType',
        'range' => 'Symfony\Component\Form\Extension\Core\Type\RangeType',
        'repeated' => 'Symfony\Component\Form\Extension\Core\Type\RepeatedType',
        'search' => 'Symfony\Component\Form\Extension\Core\Type\SearchType',
        'textarea' => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
        'text' => 'Symfony\Component\Form\Extension\Core\Type\TextType',
        'time' => 'Symfony\Component\Form\Extension\Core\Type\TimeType',
        'timezone' => 'Symfony\Component\Form\Extension\Core\Type\TimezoneType',
        'url' => 'Symfony\Component\Form\Extension\Core\Type\UrlType',
        'button' => 'Symfony\Component\Form\Extension\Core\Type\ButtonType',
        'submit' => 'Symfony\Component\Form\Extension\Core\Type\SubmitType',
        'reset' => 'Symfony\Component\Form\Extension\Core\Type\ResetType',
        'entity' => 'Symfony\Bridge\Doctrine\Form\Type\EntityType',
    ];

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof String_) {
            return false;
        }

        if (! isset($this->nameToClassMap[$node->value])) {
            return false;
        }

        if ($this->isParentTypeAndMethod($node, 'Symfony\Component\Form\AbstractType', 'getParent')) {
            return true;
        }

        if ($this->isParentTypeAndMethod($node, 'Symfony\Component\Form\AbstractTypeExtension', 'getExtendedType')) {
            return true;
        }

        return false;
    }

    /**
     * @param String_ $stringNode
     */
    public function refactor(Node $stringNode): ?Node
    {
        $class = $this->nameToClassMap[$stringNode->value];

        return $this->nodeFactory->createClassConstantReference($class);
    }

    private function isParentTypeAndMethod(Node $node, string $type, string $method): bool
    {
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($parentClassName !== $type) {
            return false;
        }

        $methodName = $node->getAttribute(Attribute::METHOD_NAME);

        return $methodName === $method;
    }
}
