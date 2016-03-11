<?php
namespace Alsatian\FormBundle\Form\Extensions;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Alsatian\FormBundle\Form\Select2ChoiceType;
use Alsatian\FormBundle\Form\Select2DocumentType;
use Alsatian\FormBundle\Form\Select2EntityType;

use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Select2Subscriber implements EventSubscriberInterface
{
    private $enabledTypes;

    private $em = null;
    private $dm = null;
    private $accessor = null;
    
    public function __contruct($enabledTypes)
    {
        $this->enabledTypes = $enabledTypes;
    }
 
    public function setEntityManager($em)
    {
        $this->em = $em;
    }

    public function setDocumentManager($dm)
    {
        $this->dm = $dm;
    }
    
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => array('populateAjaxChoices',-50),
            FormEvents::PRE_SUBMIT   => array('populateAjaxChoices',-50)
        );
    }
         
    private function getListenedType(ResolvedFormTypeInterface $type)
    {
        while($type){
            if(in_array(get_class($type->getInnerType()),$this->enabledTypes)){
                return get_class($type->getInnerType());
            }
            
            $type = $type->getParent();
        }
        
        return false;
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
        $child = $form->get($childName);
        $options = $child->getConfig()->getOptions();

        $choices = array();

        $data = $event->getData();
        if(is_array($data)){$property = '['.$childName.']';}
        else
        {$property = $childName;}
        
        if(!$this->isReadable($data,$property)){return;}
        
        $data = $this->getValue($data,$property);
        if(!$data){return;}
        
        switch($type)
        {
            case Select2EntityType::class :
            case Select2DocumentType::class :
                if(is_array($data) || $data instanceOf \Traversable){
                    foreach($data as $Entity){
                        $this->addChoice($choices,$Entity,$options['class'],$type);
                    }
                }
                else{
                    $this->addChoice($choices,$data,$options['class'],$type);
                }
            break;
            case Select2ChoiceType::class:
                if(is_array($data)){
                    foreach($data as $choice){
                        $choices[$choice] = $choice;
                    }
                }
                else{
                    $choices[$data] = $data;
                }
            break;
        } 

        // Find something better to get options
        $newOptions = array('route'=>$options['route'],'required'=>$options['required'],'multiple'=>$options['multiple'],'choices'=>$choices);
        if(array_key_exists('class',$options)){$newOptions=array_merge($newOptions,array('class'=>$options['class']));}
        $form->add($childName,$type,$newOptions);
    }

    private function addChoice(&$array,$data,$class,$type){
        if(is_object($data)){
             $array[] = $data;
        }
        else{
            switch($type){
                case Select2EntityType::class :
                    $array[] = $this->findEm($class,$data);
                break;
                case Select2DocumentType::class :
                    $array[] = $this->findDm($class,$data);
                break;
            }
        }
    }
    
    private function isReadable($data,$property){
        if(!$this->accessor){
            $this->accessor = PropertyAccess::createPropertyAccessorBuilder();
        }
        
        return $this->accessor->isReadable($data,$property);
    }
    
    private function getValue($data,$property){
        if(!$this->accessor){
            $this->accessor = PropertyAccess::createPropertyAccessorBuilder();
        }
        
        return $this->accessor->getValue($data,$property);
    }
    
    private function findEm($class,$data)
    {
        if(null === $this->em){
            $this->setEntityManager();
        }
        
        return $this->em->getRepository($class)->findOneById($data);
    }

    private function findDm($class,$data)
    {
        if(null === $this->dm){
            $this->setDocumentManager();
        }
        
        return $this->dm->getRepository($class)->findOneById($data);
    }
}
