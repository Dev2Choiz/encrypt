<?php

namespace Dev\EncryptBundle\Form;

use Dev\EncryptBundle\Annotation\Task;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeTaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $listTastType = $this->getTaskType();

        $builder->add('typeTaskList', ChoiceType::class, array(
            'choices' => $listTastType['choices'],
            'label' => 'Type de tâche',
            'required' => true,
            'expanded' => false,
            'multiple' => false,
            'mapped'   => false
        ));
    }

    private function getTaskType()
    {
        $reader = new AnnotationReader();
        $path = __DIR__;
        $list = [];
        $finder = Finder::create()->files()->in($path)->name("*Type.php");
        foreach ($finder as $file) {
            $className = 'Dev\EncryptBundle\Form\\'
                         . str_replace('.php', '', $file->getRelativePathname());
            $value = str_replace('Type.php', '', $file->getRelativePathname());
            $reflection = new \ReflectionClass($className);
            $annotations = $reader->getClassAnnotation($reflection, Task::class);

            if (null !== $annotations) {
                $label = $annotations->getLabelle();
                $list ['choices'][$label] = $value;
            }
        }
        arsort($list ['choices'], SORT_STRING);
        
        return $list;
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return '';
    }
}
