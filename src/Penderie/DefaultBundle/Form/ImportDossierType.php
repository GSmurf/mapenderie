<?php

namespace Penderie\DefaultBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImportDossierType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder->add('file', 'file', array('label'=>'Fichier csv à importer'));
   	}
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Penderie\DefaultBundle\Entity\Dossier'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'siplec_cdbundle_import_dossier';
    }
}
