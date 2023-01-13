<?php
namespace Alsatian\FormBundle\Form\Extensions;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\FormType;

class ExtensibleExtension extends AbstractTypeExtension
{
    private $extensibleSubscriber;

    public function __construct($extensibleSubscriber) {
        $this->extensibleSubscriber = $extensibleSubscriber;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($builder->getForm()->isRoot())
        {
            $builder->addEventSubscriber($this->extensibleSubscriber);
        }
    }
    
    public function getExtendedType()
    {
        return FormType::class;
    }

    public static function getExtendedTypes()
    {
        return [FormType::class];
    }
}
