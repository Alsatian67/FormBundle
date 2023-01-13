<?php
namespace Alsatian\FormBundle\Form\Extensions;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

use Alsatian\FormBundle\Form\ExtensibleChoiceType;
use Alsatian\FormBundle\Form\ExtensibleDocumentType;
use Alsatian\FormBundle\Form\ExtensibleEntityType;

class ExtensibleSubscriber implements EventSubscriberInterface
{
    private $enabledTypes;
    private $accessor = null;
    
    public function __construct($enabledTypes)
    {
        $this->enabledTypes = $enabledTypes;
    }
    
    public static function getSubscribedEvents(): array
    {
        return array(
            FormEvents::PRE_SET_DATA => array('populateAjaxChoices',-50),
            FormEvents::PRE_SUBMIT   => array('populateAjaxChoices',-50)
        );
    }
     
    public function populateAjaxChoices(FormEvent $event)
    {
        foreach($event->getForm()->all() as $child){
            if($type = $this->getListenedType($child->getConfig()->getType())){
                $this->populateAjaxChoice($event,$child->getName(),$type);
            }
        }
    }
     
    private function populateAjaxChoice(FormEvent $event,$childName, $type)
    {   
        $form = $event->getForm();
        $options = $form->get($childName)->getConfig()->getOptions();

        $data = $event->getData();
        if(is_array($data)){
            $property = '['.$childName.']';
        }
        else{
            $property = $childName;
        }
        
        if(is_null($data)){return;}
        if(!$this->isReadable($data,$property)){return;}
        
        $data = $this->getValue($data,$property);
        if(!$data){return;}
        
        switch($type['listenedType'])
        {
            case ExtensibleChoiceType::class:
                if(is_array($data)){
                    $choices = array_combine($data,$data);
                }
                else{
                    $choices = array($data => $data);
                }
            
            break;
            case ExtensibleDocumentType::class :
                $choices = $this->getChoices($data,$options);

                // These lines are to avoid 'You cannot set both an "em" and "document_manager" option.'
                // DocumentType moves 'document_manager' option to 'em' option, here we do first the inverse ...
                // See DoctrineMongoDBBundle issue #377
                
                $options['document_manager'] = $options['em'];
                unset($options['em']);

            break;
            case ExtensibleEntityType::class :
                $choices = $this->getChoices($data,$options);
            
            break;
        }

        $options['choices'] = $choices;

        $form->add($childName,$type['originalType'],$options);
    }
         
    private function getListenedType(ResolvedFormTypeInterface $type)
    {
        $return = array('originalType'  => get_class($type->getInnerType()));

        while($type){
            if(in_array(get_class($type->getInnerType()),$this->enabledTypes)){
                $return['listenedType'] = get_class($type->getInnerType());

                return $return;
            }
            
            $type = $type->getParent();
        }
        
        return false;
    }
    
    private function getPropertyAccessor()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }
    
    private function isReadable($data,$property){
        if(!$this->accessor){
            $this->getPropertyAccessor();
        }
        
        return $this->accessor->isReadable($data,$property);
    }
    
    private function getValue($data,$property){
        if(!$this->accessor){
            $this->getPropertyAccessor();
        }
        
        return $this->accessor->getValue($data,$property);
    }

    private function getChoices($data,$options){
        if(is_object($data)){
            if($data instanceOf \Traversable){
                return $data;
            }
            else{
                return array($data);
            }
        }
        else{
            if(is_string($options['choice_value']) || method_exists($options['choice_value'], '__toString')){
                $converter = new CamelCaseToSnakeCaseNameConverter(null,false);
                $method = 'findBy' . $converter->denormalize((string)$options['choice_value']);
                return $options['em']->getRepository($options['class'])->$method($data);
            }
            else{
                return $options['em']->getRepository($options['class'])->findById($data);
            }
        }
    }
}
