<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\CustomObjectsBundle\Provider;

use Symfony\Component\Routing\RouterInterface;
use MauticPlugin\CustomObjectsBundle\Exception\ForbiddenException;

class CustomItemRouteProvider
{
    public const ROUTE_LIST   = 'mautic_custom_item_list';
    public const ROUTE_VIEW   = 'mautic_custom_item_view';
    public const ROUTE_EDIT   = 'mautic_custom_item_edit';
    public const ROUTE_CLONE  = 'mautic_custom_item_clone';
    public const ROUTE_DELETE = 'mautic_custom_item_delete';
    public const ROUTE_NEW    = 'mautic_custom_item_new';
    public const ROUTE_CANCEL = 'mautic_custom_item_cancel';
    public const ROUTE_SAVE   = 'mautic_custom_item_save';

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param int $objectId
     * @param int $page
     * 
     * @throws ForbiddenException
     */
    public function buildListRoute(int $objectId, int $page = 1): string
    {
        return $this->router->generate(static::ROUTE_LIST, ['objectId' => $objectId, 'page' => $page]);
    }

    /**
     * @throws ForbiddenException
     */
    public function buildNewRoute(): string
    {
        return $this->router->generate(static::ROUTE_NEW);
    }

    /**
     * @throws ForbiddenException
     */
    public function buildSaveRoute(?int $id = null): string
    {
        return $this->router->generate(static::ROUTE_SAVE, ['objectId' => $id]);
    }

    /**
     * @param int $id
     * 
     * @throws ForbiddenException
     */
    public function buildViewRoute(int $id): string
    {
        return $this->router->generate(static::ROUTE_VIEW, ['objectId' => $id]);
    }

    /**
     * @param int $id
     * 
     * @throws ForbiddenException
     */
    public function buildEditRoute(int $id): string
    {
        return $this->router->generate(static::ROUTE_EDIT, ['objectId' => $id]);
    }

    /**
     * @param int $id
     * 
     * @throws ForbiddenException
     */
    public function buildCloneRoute(int $id): string
    {
        return $this->router->generate(static::ROUTE_CLONE, ['objectId' => $id]);
    }

    /**
     * @param int $id
     * 
     * @throws ForbiddenException
     */
    public function buildDeleteRoute(int $id): string
    {
        return $this->router->generate(static::ROUTE_DELETE, ['objectId' => $id]);
    }
}