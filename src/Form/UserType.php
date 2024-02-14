<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Username',TextType::class,["label" => "Pseudo"])
            ->add('email',EmailType::class,["label" => "Email","attr" => [
                'placeholder' => 'exemple@gmail.com'
            ]])
            
            ->add('password',RepeatedType::class,[
                "type" => PasswordType::class,
                "first_options" => ["label" => "Mot de passe","attr" => [
                    'placeholder' => '********'
                            ]],
                "second_options" => ["label" => "Confirmation","attr" => [
                    'placeholder' => '********'
                            ]]
            ])

            ->add('DateNaissance', DateTimeType::class, [
                'widget' => 'single_text'])
            
            ->add('Numero', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your phone number',
                    ]),
                    new Regex([
                        'pattern' => '/^[0-9]{8}$/',
                        'message' => 'Phone number must contain exactly 8 digits',
                    ]),
                ],
            ])        
            
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
                $user = $event->getData();
                $form = $event->getForm();

                $roles = [
                    'Client' => 'ROLE_CLIENT',
                    'Coatch' => 'ROLE_COACH',
                ];

                $form->add('roles',ChoiceType::class,[
                    "label" =>"Choisir le role",
                    'choices' => $roles,
                    'multiple' => true,
                    'expanded' => true, // Afficher comme cases à cocher
                            
                ]); 

                

                // Ajoutez le champ numéro de carte pour les coachs uniquement
               

            })
            ->add('valider',SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
