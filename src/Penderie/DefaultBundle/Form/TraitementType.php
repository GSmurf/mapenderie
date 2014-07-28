<?php

namespace Penderie\DefaultBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class TraitementType extends AbstractType
{
	var $etape = 1;
	
	public function __construct($etape)
	{
		$this->etape = $etape;
	}
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	switch($this->etape)
    	{
    		case 1:
    			$builder
    			->add('numDossier', 'text', array('label' => 'Numéro de dossier'));
    			break;
    		case 2:
    			$builder
    			->add('numDossier', 'text', array('label' => 'Numéro de dossier',
    											  'read_only' => true))
    			->add('acticall', 'checkbox', array('label' => 'Dossier ActiCall',
    					'required' => false))
    			->add('statut', 'entity', array('class' => 'PenderieDefaultBundle:StatutDossier',
    											'query_builder' => function(EntityRepository $er) {return $er->getRequeteStatutTraitementSecondEtape();},
    											'label' => 'Statut',
    											'empty_value' => '',
    											'empty_data' => null));
    			break;
    	}
    	
        
    }
    
    /**
     * @return string
     */
    public function getName()
    {
    	return 'siplec_cdbundle_traitement';
    }
    
}
