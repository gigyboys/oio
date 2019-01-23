<?php

namespace App\Form;

use App\Model\UserPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currentPassword', PasswordType::class)
            ->add('newPassword', PasswordType::class)
            ->add('repeatPassword',  PasswordType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => UserPassword::class,
            'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix() {
        return null;
    }
}