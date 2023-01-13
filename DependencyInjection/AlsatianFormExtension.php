<?php
namespace Alsatian\FormBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Loader;

class AlsatianFormExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $configFormBundle = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        $container->setParameter('alsatian_form.parameters.autocomplete.attr_class', $configFormBundle['autocomplete']['attr_class']);
        $container->setParameter('alsatian_form.parameters.date_picker.attr_class', $configFormBundle['date_picker']['attr_class']);
        $container->setParameter('alsatian_form.parameters.datetime_picker.attr_class', $configFormBundle['datetime_picker']['attr_class']);
        $container->setParameter('alsatian_form.parameters.extensible_choice.attr_class', $configFormBundle['extensible_choice']['attr_class']);
        $container->setParameter('alsatian_form.parameters.extensible_document.attr_class', $configFormBundle['extensible_document']['attr_class']);
        $container->setParameter('alsatian_form.parameters.extensible_entity.attr_class', $configFormBundle['extensible_entity']['attr_class']);
        
        if($configFormBundle['autocomplete']['enabled']){
            $definition = $container->getDefinition('alsatian_form.form_type.autocomplete');
            $definition->setPublic(true);
            $definition->addTag('form.type');
        }
        
        if($configFormBundle['date_picker']['enabled']){
            if (!class_exists('Symfony\Component\HttpFoundation\RequestStack')) {
                throw new LogicException('DatePicker type cannot be enabled as symfony/http-foundation is not installed. Try running "composer require symfony/http-foundation.');
            }
			
            $definition = $container->getDefinition('alsatian_form.form_type.date_picker');
            $definition->setPublic(true);
            $definition->addTag('form.type');
        }
        
        if($configFormBundle['datetime_picker']['enabled']){
            if (!class_exists('Symfony\Component\HttpFoundation\RequestStack')) {
                throw new LogicException('DateTimePicker type cannot be enabled as symfony/http-foundation is not installed. Try running "composer require symfony/http-foundation.');
            }
			
            $definition = $container->getDefinition('alsatian_form.form_type.datetime_picker');
            $definition->setPublic(true);
            $definition->addTag('form.type');
        }
        
        $usedExtensibleTypes = array();
        
        if($configFormBundle['extensible_choice']['enabled']){
            $definition = $container->getDefinition('alsatian_form.form_type.extensible_choice');
            $definition->setPublic(true);
            $definition->addTag('form.type');
            
            $usedExtensibleTypes[] = $definition->getClass();
        }
        
        if($configFormBundle['extensible_document']['enabled']){
            if (!class_exists('Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType')) {
                throw new LogicException('ExtensibleDocument type cannot be enabled as doctrine/mongodb-odm-bundle is not installed. Try running "composer require doctrine/mongodb-odm-bundle.');
            }
            
            if (!class_exists('Symfony\Component\Serializer\Serializer')) {
                throw new LogicException('ExtensibleDocument type cannot be enabled as the Serializer component is not installed. Try running "composer require symfony/serializer".');
            }
            
            $definition = $container->getDefinition('alsatian_form.form_type.extensible_document');
            $definition->setPublic(true);
            $definition->addTag('form.type');
            
            $usedExtensibleTypes[] = $definition->getClass();
        }
        
        if($configFormBundle['extensible_entity']['enabled']){
            if (!class_exists('Symfony\Bridge\Doctrine\Form\Type\EntityType')) {
                throw new LogicException('ExtensibleEntity type cannot be enabled as symfony/doctrine-bridge is not installed. Try running "composer require symfony/doctrine-bridge".');
            }
            
            if (!class_exists('Symfony\Component\Serializer\Serializer')) {
                throw new LogicException('ExtensibleEntity type cannot be enabled as the Serializer component is not installed. Try running "composer require symfony/serializer".');
            }
            
            $definition = $container->getDefinition('alsatian_form.form_type.extensible_entity');
            $definition->setPublic(true);
            $definition->addTag('form.type');
            
            $usedExtensibleTypes[] = $definition->getClass();
        }
                
        if($usedExtensibleTypes){
            if (!class_exists('Symfony\Component\EventDispatcher\EventDispatcher')) {
                throw new LogicException('Extensible types cannot be enabled as the EventDispatcher component is not installed. Try running "composer require symfony/event-dispatcher".');
            }
            
            if (!class_exists('Symfony\Component\PropertyAccess\PropertyAccess')) {
                throw new LogicException('ExtensibleDocument type cannot be enabled as the PropertyAccess component is not installed. Try running "composer require symfony/property-access".');
            }
            
            $definition = $container->getDefinition('alsatian_form.form_extension.extensible');
            $definition->setPublic(true);
            $definition->addTag('form.type_extension', array('extended_type'=>'Symfony\Component\Form\Extension\Core\Type\FormType'));
            
            $container->setParameter('alsatian_form.parameters.extensible.enabled_Types', $usedExtensibleTypes);
        }
    }
}
