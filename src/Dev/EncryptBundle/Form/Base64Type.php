<?php

namespace Dev\EncryptBundle\Form;

use Dev\EncryptBundle\Annotation\Task;
use Dev\EncryptBundle\Service\TaskTypeHandler\Base64Handler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class Base64Type
 *
 * @Task(labelle="Base 64")
 */
class Base64Type extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('text', TextareaType::class, array(
            'required' => true,
        ));

        $builder->add('action', ChoiceType::class, array(
            'choices' => $this->getChoices()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return '';
    }
    
    public function getChoices()
    {
        return [
            'Encode' => Base64Handler::ENCODE,
            'Decode' => Base64Handler::DECODE
        ];
    }
}
