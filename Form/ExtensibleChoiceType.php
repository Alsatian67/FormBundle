<?php
namespace Alsatian\FormBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ExtensibleChoiceType extends AbstractExtensibleChoicesType
{
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
