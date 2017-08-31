<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Deprecation\SetNames;
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
    ];

    public function getSetName(): string
    {
        return SetNames::SYMFONY;
    }

    public function sinceVersion(): float
    {
        return 4.0;
    }

    public function isCandidate(Node $node): bool
    {
        return $node instanceof String_ && isset($this->nameToClassMap[$node->value]);
    }

    /**
     * @param String_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $class = $this->nameToClassMap[$node->value];
        $nameNode = new Name('\\' . $class);

        return new ClassConstFetch($nameNode, 'class');
    }
}
