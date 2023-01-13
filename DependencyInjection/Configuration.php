<?php
namespace Alsatian\FormBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('alsatian_form');

        if(method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } 
        else{
            $rootNode = $treeBuilder->root('alsatian_form');
        }
        
        $rootNode
            ->append($this->getNode('extensible_choice'))
            ->append($this->getNode('extensible_document'))
            ->append($this->getNode('extensible_entity'))
            ->append($this->getNode('autocomplete'))
            ->append($this->getNode('date_picker'))
            ->append($this->getNode('datetime_picker'))
            ;
        
        return $treeBuilder;
    }
    
    private function getNode($name)
    {
        $treeBuilder = new TreeBuilder($name);

        if(method_exists($treeBuilder, 'getRootNode')) {
            $node = $treeBuilder->getRootNode();
        } 
        else{
            $node = $treeBuilder->root($name);
        }
        
        $node
            ->canBeEnabled()
            ->children()
                ->scalarNode('attr_class')
                    ->defaultFalse()
                ->end()
            ->end()
        ->end()
        ;
        
        return $node;
    }
}
