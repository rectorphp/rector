<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Form;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;

/**
 * Converts all:
 * $form->add('name', 'form.type.text');
 *
 * into:
 * $form->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
 *
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#frameworkbundle
 */
final class StringFormTypeToClassRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $nameToClassMap = [
        'form.type.birthday' => 'Symfony\Component\Form\Extension\Core\Type\BirthdayType',
        'form.type.checkbox' => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
        'form.type.collection' => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
        'form.type.country' => 'Symfony\Component\Form\Extension\Core\Type\CountryType',
        'form.type.currency' => 'Symfony\Component\Form\Extension\Core\Type\CurrencyType',
        'form.type.date' => 'Symfony\Component\Form\Extension\Core\Type\DateType',
        'form.type.datetime' => 'Symfony\Component\Form\Extension\Core\Type\DatetimeType',
        'form.type.email' => 'Symfony\Component\Form\Extension\Core\Type\EmailType',
        'form.type.file' => 'Symfony\Component\Form\Extension\Core\Type\FileType',
        'form.type.hidden' => 'Symfony\Component\Form\Extension\Core\Type\HiddenType',
        'form.type.integer' => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
        'form.type.language' => 'Symfony\Component\Form\Extension\Core\Type\LanguageType',
        'form.type.locale' => 'Symfony\Component\Form\Extension\Core\Type\LocaleType',
        'form.type.money' => 'Symfony\Component\Form\Extension\Core\Type\MoneyType',
        'form.type.number' => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
        'form.type.password' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
        'form.type.percent' => 'Symfony\Component\Form\Extension\Core\Type\PercentType',
        'form.type.radio' => 'Symfony\Component\Form\Extension\Core\Type\RadioType',
        'form.type.range' => 'Symfony\Component\Form\Extension\Core\Type\RangeType',
        'form.type.repeated' => 'Symfony\Component\Form\Extension\Core\Type\RepeatedType',
        'form.type.search' => 'Symfony\Component\Form\Extension\Core\Type\SearchType',
        'form.type.textarea' => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
        'form.type.text' => 'Symfony\Component\Form\Extension\Core\Type\TextType',
        'form.type.time' => 'Symfony\Component\Form\Extension\Core\Type\TimeType',
        'form.type.timezone' => 'Symfony\Component\Form\Extension\Core\Type\TimezoneType',
        'form.type.url' => 'Symfony\Component\Form\Extension\Core\Type\UrlType',
        'form.type.button' => 'Symfony\Component\Form\Extension\Core\Type\ButtonType',
        'form.type.submit' => 'Symfony\Component\Form\Extension\Core\Type\SubmitType',
        'form.type.reset' => 'Symfony\Component\Form\Extension\Core\Type\ResetType',
        'form.type.entity' => 'Symfony\Bridge\Doctrine\Form\Type\EntityType',
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

        $argNode = $node->getAttribute(Attribute::PARENT_NODE);
        if (! $argNode instanceof Arg) {
            return false;
        }

        $methodCallNode = $argNode->getAttribute(Attribute::PARENT_NODE);
        if (! $methodCallNode instanceof MethodCall) {
            return false;
        }

        return $methodCallNode->name->toString() === 'add';
    }

    /**
     * @param String_ $stringNode
     */
    public function refactor(Node $stringNode): ?Node
    {
        $class = $this->nameToClassMap[$stringNode->value];

        return $this->nodeFactory->createClassConstantReference($class);
    }
}
