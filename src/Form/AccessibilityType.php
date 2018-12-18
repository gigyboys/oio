<?php

namespace App\Form;

use App\Entity\Contact;
use App\Model\Accessibility;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccessibilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('categoriesIndex', IntegerType::class)
            ->add('schoolsByPage', IntegerType::class)
            ->add('postsByPage', IntegerType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Accessibility::class,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix() {
        return null;
    }
}
