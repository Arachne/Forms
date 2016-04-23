<?php

namespace Tests\Integration;

use Arachne\Bootstrap\Configurator;
use Codeception\TestCase\Test;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FormsExtensionTest extends Test
{

    public function testFormTypes()
    {
        $types = [
            'birthday' => 'Symfony\Component\Form\Extension\Core\Type\BirthdayType',
            'button' => 'Symfony\Component\Form\Extension\Core\Type\ButtonType',
            'checkbox' => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
            'choice' => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
            'collection' => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
            'country' => 'Symfony\Component\Form\Extension\Core\Type\CountryType',
            'currency' => 'Symfony\Component\Form\Extension\Core\Type\CurrencyType',
            'date' => 'Symfony\Component\Form\Extension\Core\Type\DateType',
            'datetime' => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            'email' => 'Symfony\Component\Form\Extension\Core\Type\EmailType',
            'file' => 'Symfony\Component\Form\Extension\Core\Type\FileType',
            'form' => 'Symfony\Component\Form\Extension\Core\Type\FormType',
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
            'reset' => 'Symfony\Component\Form\Extension\Core\Type\ResetType',
            'search' => 'Symfony\Component\Form\Extension\Core\Type\SearchType',
            'submit' => 'Symfony\Component\Form\Extension\Core\Type\SubmitType',
            'text' => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            'textarea' => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
            'time' => 'Symfony\Component\Form\Extension\Core\Type\TimeType',
            'timezone' => 'Symfony\Component\Form\Extension\Core\Type\TimezoneType',
            'url' => 'Symfony\Component\Form\Extension\Core\Type\UrlType',
        ];

        $container = $this->createContainer('config.neon');
        $registry = $container->getByType('Symfony\Component\Form\FormRegistryInterface');

        foreach ($types as $name => $type) {
            $this->assertInstanceOf($type, $type1 = $registry->getType($name)->getInnerType());
            $this->assertInstanceOf($type, $type2 = $registry->getType($type)->getInnerType());
            $this->assertSame($type1, $type2);
            $this->assertSame($name, $type1->getBlockPrefix());
        }
    }

    private function createContainer($file)
    {
        $config = new Configurator();
        $config->setTempDirectory(TEMP_DIR);
        $config->addConfig(__DIR__ . '/../config/' . $file, false);
        return $config->createContainer();
    }
}
