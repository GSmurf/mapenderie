<?php

namespace Siplec\CdBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DossierType extends AbstractType
{
	var $mode_edition = false;
	
	public function __construct($mode_edition = false){
		$this->mode_edition = $mode_edition;
	}
	
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	if(!$this->mode_edition){
    		// Présentation du formulaire dossier pour la saisie
	        $builder->add('typeDossier', 'choice', array (
		            					'choices' => array ('Nouveau'=>'Nouveau', 'Complément'=>'Complément'),
										'required' => true,
		            					'label' => 'Type de dossier', 
										'empty_data' => false 
					))
		            ->add('thematique', 'entity', array(
		            					'label' => 'Thématique', 
										'class' => 'SiplecCdBundle:Thematique',
		            					'required' => true
					))
		            ->add('commentaire')
		            ->add('numDossier', 'text', array('label'=>'Numéro de dossier'))
		     ;
    	}else{
    		// Présentation du formulaire dossier pour l'édition
	        $builder
			            ->add('numDossier', 'text', array('label'=>'Numéro de dossier'))
	        			->add('typeDossier', 'choice', array (
			            					'choices' => array ('Nouveau'=>'Nouveau', 'Complément'=>'Complément'),
											'required' => true,
			            					'label' => 'Type de dossier', 
											'empty_data' => false 
						))
			            ->add('thematique', 'entity', array(
			            					'label' => 'Thématique', 
											'class' => 'SiplecCdBundle:Thematique',
			            					'required' => true
						))
			            ->add('dateReceptionAdequation', 'date', array('label' => 'Date réception Adéquation',
            								'widget' => 'single_text',
                                                'input' => 'datetime',
                                                'format' => 'dd/MM/yyyy HH:mm',
                                                'attr' => array('class' => 'dateHeure'),
                                                ))
			            ->add('dossierPere', 'entity', array('label'=>'Dossier parent',
											'class' => 'SiplecCdBundle:Dossier',
			            					'required' => false))
			            ->add('statut', 'entity', array('label'=>'Statut',
											'class' => 'SiplecCdBundle:StatutDossier',
    										'empty_value' => false,
			            					'required' => false))
			            ->add('commentaire')
			            ->add('refRecall', 'text', array('label'=>'Référence recall',
			            					'required' => false))
			            ->add('dateReexpedition', 'date', array('label' => 'Date recall',
            								'widget' => 'single_text',
                                                'input' => 'datetime',
                                                'format' => 'dd/MM/yyyy HH:mm',
                                                'attr' => array('class' => 'dateHeure'),
		            							'required' => false
                                                ))
			            ->add('dateRefus', 'date', array('label' => 'Date de refus automatique',
            								'widget' => 'single_text',
                                                'input' => 'datetime',
                                                'format' => 'dd/MM/yyyy HH:mm',
                                                'attr' => array('class' => 'dateHeure'),
		            							'required' => false
                                                ))
			        ;
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
        return 'siplec_cdbundle_dossier';
    }
}
