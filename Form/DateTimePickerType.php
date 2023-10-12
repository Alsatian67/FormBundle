<?php
namespace Alsatian\FormBundle\Form;

use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimePickerType extends AbstractType
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
    public function configureOptions(OptionsResolver $resolver): void
    {
        $intl = new \IntlDateFormatter($this->request_stack->getCurrentRequest()->getLocale(), \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
		$pattern = $intl->getPattern();

        $resolver->setDefaults(array('date_format'=>$pattern,'date_widget'=>'single_text','time_widget'=>'single_text'));

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
        return DateTimeType::class;
    }
	
    public function getBlockPrefix(): string
    {
        return 'date_time';
    }
}
