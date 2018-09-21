<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Form\Helper;

use Nette\Utils\Strings;

final class FormTypeStringToTypeProvider
{
    /**
     * @var string[]
     */
    private static $nameToTypeMap = [
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

    public function matchClassForNameWithPrefix(string $name): ?string
    {
        if (Strings::startsWith($name, 'form.type.')) {
            $name = Strings::substring($name, strlen('form.type.'));
        }

        if (! isset(self::$nameToTypeMap[$name])) {
            return null;
        }

        return self::$nameToTypeMap[$name];
    }
}
