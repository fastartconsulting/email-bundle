<?php

namespace FAC\EmailBundle\Form;

use FAC\EmailBundle\Entity\TemplateNewsletter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsletterType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('summary',           TextType::class,    array('required'=>false,     'mapped'=>true))
            ->add('sendOn',            DateTimeType::class,        array('required'=>true,   'mapped'=>true, 'widget' => 'single_text', 'format' => 'yyyy-MM-dd H:i:s'))
            ->add('isDraft',           ChoiceType::class, array(
                'choices'  => array(
                    '0' => false,
                    '1' => true,
                ),
            ))
            ->add('sendNow',           ChoiceType::class, array(
                'choices'  => array(
                    '0' => false,
                    '1' => true,
                ),
            ))
            ->add('templateNewsletter',             EntityType::class,  array('required'=>true,     'mapped'=>true, 'class'=>TemplateNewsletter::class, 'choice_value'=>'id'))
            ->add('mailingListType',                IntegerType::class,    array('required'=>true,      'mapped'=>true))
            ->add('idBadgeStructureMailingList',    IntegerType::class,    array('required'=>false,      'mapped'=>true))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'FAC\EmailBundle\Entity\Newsletter',
        ));
    }

}