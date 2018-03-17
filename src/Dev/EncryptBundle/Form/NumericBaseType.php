<?php

namespace Dev\EncryptBundle\Form;

use Dev\EncryptBundle\Annotation\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Class NumericBaseType
 *
 * @Task(labelle="Base numérique")
 */
class NumericBaseType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('text', TextareaType::class, array(
            'required' => true,
            'constraints' => array(
                new Length(array('min' => 1)),
            ),
        ));

        $builder->add('currentBase', ChoiceType::class, array(
            'choices' => $this->getChoices()
        ));
        $builder->add('whishedBase', ChoiceType::class, $builder->get('currentBase')->getOptions());
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
        $choices = [];
        $choices ['ASCII'] = 'ASCII';
        for ($i = 2; $i <= 36; ++$i) {
            switch ($i) {
                case 2:
                    $label = 'Binaire';
                    break;
                case 8:
                    $label = 'Octal';
                    break;
                case 10:
                    $label = 'Decimal';
                    break;
                case 16:
                    $label = 'Hexadecimal';
                    break;
                default:
                    $label = $i;
                    break;
            }
            $choices [$label] = $i;
        }
        return $choices;
    }
}
