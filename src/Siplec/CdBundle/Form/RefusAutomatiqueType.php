<?php

namespace Siplec\CdBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RefusAutomatiqueType extends AbstractType
{
	var $importExcel = false;
	
	public function __construct($importExcel = false){
		$this->importExcel = $importExcel;
	}
	
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	if(!$this->importExcel){
    		// Présentation du formulaire dossier pour la saisie
	        $builder->add('numDossier', 'text', array('label'=>'Numéro de dossier',
	        											'attr' => array('class' => 'numDossier'))
	        )
		     ;
    	}else{
    		// Présentation du formulaire dossier pour l'édition
	        $builder->add('file', 'file', array('label'=>'Fichier csv à importer'));
    	}
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Siplec\CdBundle\Entity\Dossier'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'siplec_cdbundle_refus_auto';
    }
}
