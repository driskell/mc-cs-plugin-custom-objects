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

namespace MauticPlugin\CustomObjectsBundle\Controller;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use MauticPlugin\CustomObjectsBundle\Entity\CustomField;
use MauticPlugin\CustomObjectsBundle\Form\Type\CustomFieldType;
use MauticPlugin\CustomObjectsBundle\Model\CustomFieldModel;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Mautic\CoreBundle\Controller\CommonController;
use Symfony\Component\HttpFoundation\JsonResponse;
use MauticPlugin\CustomObjectsBundle\Exception\NotFoundException;
use MauticPlugin\CustomObjectsBundle\Provider\CustomFieldPermissionProvider;
use MauticPlugin\CustomObjectsBundle\Exception\ForbiddenException;

class CustomFieldSaveController extends CommonController
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var CustomFieldModel
     */
    private $customFieldModel;

    /**
     * @var CustomFieldPermissionProvider
     */
    private $permissionProvider;

    /**
     * @param RequestStack $requestStack
     * @param Router $router
     * @param Session $session
     * @param FormFactory $formFactory
     * @param TranslatorInterface $translator
     * @param CustomFieldModel $customFieldModel
     * @param CustomFieldPermissionProvider $permissionProvider
     */
    public function __construct(
        RequestStack $requestStack,
        Router $router,
        Session $session,
        FormFactory $formFactory,
        TranslatorInterface $translator,
        CustomFieldModel $customFieldModel,
        CustomFieldPermissionProvider $permissionProvider
    )
    {
        $this->requestStack       = $requestStack;
        $this->router             = $router;
        $this->session            = $session;
        $this->formFactory        = $formFactory;
        $this->translator         = $translator;
        $this->customFieldModel  = $customFieldModel;
        $this->permissionProvider = $permissionProvider;
    }

    /**
     * @param int|null $objectId
     * 
     * @return Response|JsonResponse
     */
    public function saveAction(?int $objectId = null)
    {
        try {
            $entity = $objectId ? $this->customFieldModel->getEntity($objectId): new CustomField();
        } catch (NotFoundException $e) {
            return $this->notFound($e->getMessage());
        }

        try {
            if ($entity->isNew()) {
                $this->permissionProvider->canCreate();
            } else {
                $this->permissionProvider->canEdit($entity);
            }
        } catch (ForbiddenException $e) {
            $this->accessDenied(false, $e->getMessage());
        }

        $request = $this->requestStack->getCurrentRequest();
        $action  = $this->router->generate('mautic_custom_field_save', ['objectId' => $objectId]);
        $form    = $this->formFactory->create(CustomFieldType::class, $entity, ['action' => $action]);
        $form->handleRequest($request);

        // $validator = $this->get('validator');
        // $errors = $validator->validate($entity);
        
        if ($form->isValid()) {
            $this->customFieldModel->save($entity);

            $this->session->getFlashBag()->add(
                'notice',
                $this->translator->trans(
                    $objectId ? 'mautic.core.notice.updated' : 'mautic.core.notice.created',
                    [
                        '%name%' => $entity->getName(),
                        '%url%'  => $this->router->generate(
                            'mautic_custom_field_edit',
                            ['objectId' => $entity->getId()]
                        ),
                    ], 
                    'flashes'
                )
            );

            if ($form->get('buttons')->get('save')->isClicked()) {
                return $this->redirectToDetail($request, $entity);
            } else {
                return $this->redirectToEdit($request, $entity);
            }
        }

        return $this->delegateView(
            [
                'returnUrl'      => $this->router->generate('mautic_custom_field_new'),
                'viewParameters' => [
                    'entity' => $entity,
                    'form'   => $form->createView(),
                    'tmpl'   => $request->isXmlHttpRequest() ? $request->get('tmpl', 'index') : 'index',
                ],
                'contentTemplate' => 'CustomObjectsBundle:CustomField:form.html.php',
                'passthroughVars' => [
                    'mauticContent' => 'customField',
                    'route'         => $this->router->generate('mautic_custom_field_new'),
                ],
            ]
        );
    }

    /**
     * @param Request     $request
     * @param CustomField $entity
     * 
     * @return Response
     */
    private function redirectToEdit(Request $request, CustomField $entity): Response
    {
        $request->setMethod('GET');
        $params = ['objectId' => $entity->getId()];

        return $this->forward('custom_field.edit_controller:renderFormAction', $params);
    }

    /**
     * @param Request     $request
     * @param CustomField $entity
     * 
     * @return Response
     */
    private function redirectToDetail(Request $request, CustomField $entity): Response
    {
        $request->setMethod('GET');
        $params = ['objectId' => $entity->getId()];

        return $this->forward('CustomObjectsBundle:CustomFieldView:view', $params);
    }
}