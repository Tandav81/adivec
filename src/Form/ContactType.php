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
use Symfony\Component\Validator\Constraints as Assert;
use VictorPrdh\RecaptchaBundle\Form\ReCaptchaType;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('company', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez indiquer votre société.']),
                    new Assert\Length(['max' => 150, 'maxMessage' => 'Le nom de la société ne peut pas dépasser {{ limit }} caractères.']),
                ],
            ])
            ->add('nom', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez indiquer votre nom.']),
                    new Assert\Length(['max' => 100, 'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.']),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez indiquer votre adresse email.']),
                    new Assert\Email(['message' => 'L\'adresse email "{{ value }}" n\'est pas valide.']),
                ],
            ])
            ->add('numTel', TelType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 20, 'maxMessage' => 'Le numéro de téléphone ne peut pas dépasser {{ limit }} caractères.']),
                ],
            ])
            ->add('codePostal', NumberType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 10, 'maxMessage' => 'Le code postal ne peut pas dépasser {{ limit }} caractères.']),
                ],
            ])
            ->add('message', TextareaType::class, [
                'attr' => ['rows' => 6],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez écrire votre message.']),
                    new Assert\Length([
                        'min' => 10,
                        'minMessage' => 'Votre message doit contenir au moins {{ limit }} caractères.',
                        'max' => 2000,
                        'maxMessage' => 'Votre message ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('captcha', ReCaptchaType::class, [
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
