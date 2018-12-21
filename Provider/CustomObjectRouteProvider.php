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

use MauticPlugin\CustomObjectsBundle\Provider\StandardRouteProvider;

class CustomObjectRouteProvider extends StandardRouteProvider
{
    public const ROUTE_LIST   = 'mautic_custom_object_list';
    public const ROUTE_VIEW   = 'mautic_custom_object_view';
    public const ROUTE_EDIT   = 'mautic_custom_object_edit';
    public const ROUTE_CLONE  = 'mautic_custom_object_clone';
    public const ROUTE_DELETE = 'mautic_custom_object_delete';
    public const ROUTE_NEW    = 'mautic_custom_object_new';
    public const ROUTE_CANCEL = 'mautic_custom_object_cancel';
    public const ROUTE_SAVE   = 'mautic_custom_object_save';
}