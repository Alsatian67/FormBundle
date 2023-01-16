<?php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Alsatian\FormBundle\Form\AbstractRoutableType;

use Alsatian\FormBundle\Form\Extensions\ExtensibleExtension;
use Alsatian\FormBundle\Form\Extensions\ExtensibleSubscriber;
use Alsatian\FormBundle\Form\AbstractExtensibleChoicesType;
use Alsatian\FormBundle\Form\ExtensibleChoiceType;
use Alsatian\FormBundle\Form\ExtensibleDocumentType;
use Alsatian\FormBundle\Form\ExtensibleEntityType;

use Alsatian\FormBundle\Form\AutocompleteType;
use Alsatian\FormBundle\Form\DatePickerType;
use Alsatian\FormBundle\Form\DateTimePickerType;

return function(ContainerConfigurator $container) {
    $container->services()
        ->set('alsatian_form.form_type.abstract_routable', AbstractRoutableType::class)
            ->abstract(true)
            ->args([
                service('router')
            ])
            
        // Extensible types :
        ->set('alsatian_form.form_extension.extensible', ExtensibleExtension::class)
            ->args([
                service('alsatian_form.form_event_subscriber.extensible')
            ])
                     
        ->set('alsatian_form.form_event_subscriber.extensible', ExtensibleSubscriber::class)
            ->args([
                param('alsatian_form.parameters.extensible.enabled_Types')
            ])

        ->set('alsatian_form.form_type.abstract_extensible', AbstractExtensibleChoicesType::class)
            ->parent('alsatian_form.form_type.abstract_routable')
            ->abstract(true)

        ->set('alsatian_form.form_type.extensible_choice', ExtensibleChoiceType::class)
            ->parent('alsatian_form.form_type.abstract_extensible')
            ->args([
                param('alsatian_form.parameters.extensible_choice.attr_class')
            ])

        ->set('alsatian_form.form_type.extensible_document', ExtensibleDocumentType::class)
            ->parent('alsatian_form.form_type.abstract_extensible')
            ->args([
                param('alsatian_form.parameters.extensible_document.attr_class')
            ])

        ->set('alsatian_form.form_type.extensible_entity', ExtensibleEntityType::class)
            ->parent('alsatian_form.form_type.abstract_extensible')
            ->args([
                param('alsatian_form.parameters.extensible_entity.attr_class')
            ])
                    
        // Other types 
        ->set('alsatian_form.form_type.autocomplete', AutocompleteType::class)
            ->parent('alsatian_form.form_type.abstract_routable')
            ->args([
                param('alsatian_form.parameters.autocomplete.attr_class')
            ])

        ->set('alsatian_form.form_type.date_picker', DatePickerType::class)
            ->args([
                service('request_stack'),
                param('alsatian_form.parameters.date_picker.attr_class')
            ])

        ->set('alsatian_form.form_type.datetime_picker', DateTimePickerType::class)
            ->args([
                service('request_stack'),
                param('alsatian_form.parameters.datetime_picker.attr_class')
            ])
    ;
};
