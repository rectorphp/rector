<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Form\Helper;

use Nette\Utils\Strings;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;

final class FormTypeStringToTypeProvider
{
    /**
     * @var string[]
     */
    private static $nameToTypeMap = [
        'form' => FormType::class,
        'birthday' => BirthdayType::class,
        'checkbox' => CheckboxType::class,
        'collection' => CollectionType::class,
        'country' => CountryType::class,
        'currency' => CurrencyType::class,
        'date' => DateType::class,
        'datetime' => 'Symfony\Component\Form\Extension\Core\Type\DatetimeType',
        'email' => EmailType::class,
        'file' => FileType::class,
        'hidden' => HiddenType::class,
        'integer' => IntegerType::class,
        'language' => LanguageType::class,
        'locale' => LocaleType::class,
        'money' => MoneyType::class,
        'number' => NumberType::class,
        'password' => PasswordType::class,
        'percent' => PercentType::class,
        'radio' => RadioType::class,
        'range' => RangeType::class,
        'repeated' => RepeatedType::class,
        'search' => SearchType::class,
        'textarea' => TextareaType::class,
        'text' => TextType::class,
        'time' => TimeType::class,
        'timezone' => TimezoneType::class,
        'url' => UrlType::class,
        'button' => ButtonType::class,
        'submit' => SubmitType::class,
        'reset' => ResetType::class,
        'entity' => 'Symfony\Bridge\Doctrine\Form\Type\EntityType',
    ];

    public function hasClassForNameWithPrefix(string $name): bool
    {
        $name = $this->removeFormTypePrefix($name);

        return $this->hasClassForName($name);
    }

    public function getClassForNameWithPrefix(string $name): string
    {
        $name = $this->removeFormTypePrefix($name);

        return $this->getClassForName($name);
    }

    public function hasClassForName(string $name): bool
    {
        return array_key_exists($name, self::$nameToTypeMap);
    }

    public function getClassForName(string $name): string
    {
        return self::$nameToTypeMap[$name];
    }

    private function removeFormTypePrefix(string $name): string
    {
        return Strings::substring($name, strlen('form.type.'));
    }
}
