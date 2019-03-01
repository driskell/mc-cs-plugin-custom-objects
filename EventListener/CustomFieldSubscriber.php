<?php

declare(strict_types=1);

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\CustomObjectsBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use MauticPlugin\CustomObjectsBundle\Entity\CustomField;
use MauticPlugin\CustomObjectsBundle\Provider\CustomFieldTypeProvider;

/**
 * CustomField entity lifecycle.
 */
class CustomFieldSubscriber implements EventSubscriber
{
    /**
     * @var CustomFieldTypeProvider
     */
    private $customFieldTypeProvider;

    /**
     * @param CustomFieldTypeProvider $customFieldTypeProvider
     */
    public function __construct(CustomFieldTypeProvider $customFieldTypeProvider)
    {
        $this->customFieldTypeProvider = $customFieldTypeProvider;
    }

    /**
     * @return mixed[]
     */
    public function getSubscribedEvents(): array
    {
        return [
            'postLoad',
            'prePersist',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \MauticPlugin\CustomObjectsBundle\Exception\NotFoundException
     */
    public function postLoad(LifecycleEventArgs $args): void
    {
        $customField = $args->getObject();

        if (!$customField instanceof CustomField) {
            return;
        }

        $customField->setTypeObject($this->customFieldTypeProvider->getType($customField->getType()));

        $params = $customField->getParams();
        $params = new CustomField\Params($params);

        if ($params instanceof CustomField\Params) {
            $customField->setParamsObject($params);
        } else {
            $customField->setParamsObject(new CustomField\Params());
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $customField = $args->getObject();

        if (!$customField instanceof CustomField) {
            return;
        }

        $customField->setParams((array) $customField->getParamsObject());
    }
}
