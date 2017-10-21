<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Form\Helper;

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
