<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File ;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Email;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Username',TextType::class,["label" => "Pseudo",
            "attr" => [
                'placeholder' => 'Foulen Ben Foulen'
                        ]
            ])
            ->add('email',TextType::class,["label" => "Email","attr" => [
                'placeholder' => 'exemple@gmail.com']
                
            ])
            
            ->add('password',RepeatedType::class,[
                "type" => PasswordType::class,
                "first_options" => ["label" => "Mot de passe","attr" => [
                    'placeholder' => '********'
                            ]],
                "second_options" => ["label" => "Confirmation","attr" => [
                    'placeholder' => '********'
                            ]]
            ])

            ->add('DateNaissance', DateType::class, [
                'widget' => 'single_text'])
            
            ->add('Numero', TextType::class, [
               
            ])
            
            ->add('brochure', FileType::class, [
                'label' => 'Brochure (PDF file)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File ([
                        'maxSize' => '2024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
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
