<?php
namespace Alsatian\FormBundle\Form\Extensions;

use Symfony\Component\Form\AbstractTypeExtension;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;

class ExtensibleExtension extends AbstractTypeExtension
{
    private $extensibleSubscriber;

    public function __construct($extensibleSubscriber) {
        $this->extensibleSubscriber = $extensibleSubscriber;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if($builder->getForm()->isRoot())
        {
            $builder->addEventSubscriber($this->extensibleSubscriber);
        }
    }
    
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
