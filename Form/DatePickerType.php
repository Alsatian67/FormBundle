<?php
namespace Alsatian\FormBundle\Form;

use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatePickerType extends AbstractType
{
    protected $request_stack;
    protected $default_attr_class;
        
    public function __construct($request_stack,$default_attr_class)
    {
        $this->request_stack = $request_stack;
        $this->default_attr_class = $default_attr_class;
    }
    
   /**
    * {@inheritdoc}
    */
    public function configureOptions(OptionsResolver $resolver)
    {
        $intl = new \IntlDateFormatter($this->request_stack->getCurrentRequest()->getLocale(), \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
		$pattern = $intl->getPattern();

        $resolver->setDefaults(array('format'=>$pattern,'widget'=>'single_text'));

        $resolver->setDefault('attr', function(Options $options, $attr) use ($pattern){
            if($this->default_attr_class){
                $attr['class'] = $this->default_attr_class;
            }

			$attr['data-pattern'] = $pattern;
            
            return $attr;
        });
    }
        
    public function getParent(): ?string
    {
        return DateType::class;
    }
	
    public function getBlockPrefix(): string
    {
        return 'date';
    }
}
