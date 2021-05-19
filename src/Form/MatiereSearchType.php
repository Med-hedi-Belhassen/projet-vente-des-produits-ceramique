<?php

namespace App\Form;

use App\Entity\Matiere;
use App\Entity\MatiereSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MatiereSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('Matiers',EntityType::class,['class' => Matiere::class,'choice_label' => 'designation', 'label' => 'Matieres'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MatiereSearch::class,
        ]);
    }
}
