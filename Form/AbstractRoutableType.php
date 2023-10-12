<?php
namespace Alsatian\FormBundle\Form;

use Symfony\Component\Form\AbstractType;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractRoutableType extends AbstractType
{
    protected $router;
    protected $default_attr_class;
    
    public function __construct($router,$default_attr_class)
    {
        $this->router = $router;
        $this->default_attr_class = $default_attr_class;
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {  
        $resolver->setDefaults(array('route'=>false,'route_params'=>array()));        

        $resolver->setNormalizer('attr', function (Options $options, $attr) {
            if($this->default_attr_class){
                if (array_key_exists('class', $attr)) {
                    $attr['class'] = $this->default_attr_class . ' ' . $attr['class'];
                } else {
                    $attr['class'] = $this->default_attr_class;
                }
            }

            if($options['route'] && !array_key_exists('data-ajax--url', $attr)){
                $attr['data-ajax--url'] = $this->router->generate($options['route'], $options['route_params']);
            }

           return $attr;
        });
    }
}
