<?php

namespace Siplec\CdBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReexpeditionType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
   		// Présentation du formulaire dossier pour la saisie
        $builder->add('refRecall', 'text', array('label' => 'Référence RECALL'))
        		->add('numDossier', 'text', array('label'=>'Numéro de dossier', 'attr' => array('class' => 'numDossier'))
        );
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'siplec_cdbundle_reexpedition';
    }
}
