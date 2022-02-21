<?php

declare(strict_types=1);


namespace MauticPlugin\CustomObjectsBundle\Form\Type;

use Mautic\CategoryBundle\Form\Type\CategoryListType;
use Mautic\CoreBundle\Form\Type\FormButtonsType;
use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use MauticPlugin\CustomObjectsBundle\Entity\CustomItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class CustomItemType extends AbstractType
{
    /**
     * @param mixed[] $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var CustomItem $customItem */
        $customItem = $builder->getData();

        // Hide the name field if the item is an instance of a child object. Name will be autogenerated.
        if (null === $customItem->getCustomObject()->getMasterObject()) {
            $builder->add(
                'name',
                TextType::class,
                [
                    'label'      => 'custom.item.name.label',
                    'required'   => true,
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => ['class' => 'form-control'],
                ]
            );
        }

        $builder->add(
            'custom_field_values',
            CollectionType::class,
            [
                'entry_type'    => CustomFieldValueType::class,
                'label'         => false,
                'constraints'   => [new Valid()],
                'entry_options' => [
                    'label'      => false,
                    'customItem' => $customItem,
                ],
            ]
        );

        if ($customItem->getChildCustomItem()) {
            $builder->add(
                'child_custom_field_values',
                CollectionType::class,
                [
                    'entry_type'    => CustomFieldValueType::class,
                    'label'         => false,
                    'constraints'   => [new Valid()],
                    'entry_options' => [
                        'label'      => false,
                        'customItem' => $customItem->getChildCustomItem(),
                    ],
                ]
            );
        }

        $builder->add(
            'contact_id',
            HiddenType::class,
            [
                'mapped' => false,
                'data'   => empty($options['contactId']) ? null : $options['contactId'],
            ]
        );

        $builder->add('category', CategoryListType::class, ['bundle' => 'global']);
        $builder->add('isPublished', YesNoButtonGroupType::class);

        $cancelOnclickUrl = "mQuery('form[name=custom_item]').attr('action', mQuery('form[name=custom_item]').attr('action').replace('/save', '/cancel'));";
        if (!empty($options['cancelUrl'])) {
            $cancelOnclickUrl = sprintf("mQuery('form[name=custom_item]').attr('action', %s);", json_encode($options['cancelUrl']));
        }

        $builder->add(
            'buttons',
            FormButtonsType::class,
            [
                'cancel_onclick' => $cancelOnclickUrl,
            ]
        );

        $builder->setAction($options['action']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomItem::class,
        ]);
        $resolver->setRequired(['objectId']);
        $resolver->setDefined(['contactId', 'cancelUrl']);
    }
}
