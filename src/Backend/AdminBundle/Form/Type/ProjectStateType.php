<?php

namespace Backend\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ProjectStateType extends AbstractType
{
  public function configureOptions(OptionsResolver $resolver)
  {
      $resolver->setDefaults(array(
        'choices' => array(true => 'Oui', false => 'Non'),
        'expanded' => true,
        'multiple' => false,
        'data' => 0
      ));
  }

  public function getParent()
  {
      return ChoiceType::class;
  }
}