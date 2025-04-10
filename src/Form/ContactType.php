<?php
namespace App\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use VictorPrdh\RecaptchaBundle\Form\ReCaptchaType;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('company',TextType::class, [
            ])
            ->add('nom',TextType::class, [
            ])
            ->add('email',EmailType::class, [
            ])
            ->add('numTel',TelType::class, [
            ])
            ->add('codePostal',NumberType::class, [
            ])
            ->add('message', TextareaType::class, [
                'attr' => ['rows' => 6],
            ])
            ->add('captcha', ReCaptchaType::class, [
                'required'   => true,
            ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        ]);
    }
}
