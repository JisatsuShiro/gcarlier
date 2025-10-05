<?php

namespace App\Form;

use App\Entity\VpsServer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VpsServerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du serveur',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Mon serveur VPS']
            ])
            ->add('ipAddress', TextType::class, [
                'label' => 'Adresse IP',
                'attr' => ['class' => 'form-control', 'placeholder' => '192.168.1.1']
            ])
            ->add('sshPort', IntegerType::class, [
                'label' => 'Port SSH',
                'data' => 22,
                'attr' => ['class' => 'form-control']
            ])
            ->add('sshUser', TextType::class, [
                'label' => 'Utilisateur SSH',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'root']
            ])
            ->add('location', TextType::class, [
                'label' => 'Localisation',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Paris, France']
            ])
            ->add('provider', TextType::class, [
                'label' => 'Fournisseur',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'OVH, AWS, etc.']
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Actif' => 'active',
                    'Inactif' => 'inactive',
                    'Maintenance' => 'maintenance',
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VpsServer::class,
        ]);
    }
}
