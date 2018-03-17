<?php

namespace Dev\EncryptBundle\Form;

use Dev\EncoderBundle\Entity\FileType;
use Dev\EncoderBundle\Repository\CodingTypeRepository;
use Dev\EncryptBundle\Annotation\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 * @Task(labelle="Opération binaire")
 */
class BinaryOperationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstNumber', TextareaType::class, array(
            'required' => true,
        ));
        $builder->add('operator', ChoiceType::class, array(
            'required' => true,
            //'choices_as_values' => true,
            'choices' => array(
                'Addition' => 'addition',
                'Soustraction' => 'soustraction',
                //'Multiplication' => 'multiplication',
                //'Division' => 'division',
            ),
        ));
        $builder->add('secondNumber', TextareaType::class, array(
            'required' => true,
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
}
