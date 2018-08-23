<?php
/**
 * Created by PhpStorm.
 * User: adachauer
 * Date: 18.02.18
 * Time: 15:17
 */

namespace webworks\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Debug\Exception\ClassNotFoundException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use webworks\CoreBundle\Service\BaseService;

/**
 * Class BaseController
 * @package webworks\BackendBundle\Controller
 */
abstract class BaseController extends Controller
{

    protected $currentObj;
    protected $form;

    /**
     * @return object
     */
    protected function getCurrentObject()
    {
        return $this->currentObj;
    }

    protected function prepareObject($obj)
    {
        return $obj;
    }

    /**
     * @return Form
     */
    protected function getForm()
    {
        return $this->form;
    }

    /**
     * @return BaseService
     */
    protected abstract function getService();

    /**
     * @return string
     *
     * @todo: why does this method exist? it is only used once and the name of the method is ambiguous
     */
    private function getMainClassName()
    {
        return $this->getService()->getEntityClassName();
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function indexAction(Request $request)
    {
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $this->getService()->getAll(),
            $request->query->getInt('page', 1),
            $this->getParameter('webworks_core.pagination_items')
        );

        $params = [
            'table_alias' => $this->getService()->getTableAlias(),
            'items' => $pagination,
            'routes' => $this->getService()->getRoutes(),
        ];
        return $this->render($this->getService()->getTemplates()->getIndex(), $params);
    }

    /**
     * @param Request $request
     * @param null $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    protected function processForm(Request $request, $id = null)
    {
        $cn = $this->getMainClassName();
        $obj = new $cn();
        if (!is_null($id) && $id > 0) {
            $obj = $this->getService()->getOneById($id);
        } else {
            $obj = $this->prepareObject($obj);
        }
        $form = $this->getService()->getForm($obj);
        $form->handleRequest($request);

        $this->form = $form;

        $obj = $this->beforeSubmit($obj);

        if ($form->isSubmitted() && $form->isValid()) {
            $obj = $form->getData();

            $obj = $this->prePersist($obj);

            if (!is_null($id) && $id > 0) {
                if (method_exists($obj, 'setUpdatedBy')) {
                    $obj->setUpdatedBy($this->getUser());
                }
                if (method_exists($obj, 'setUpdatedAt')) {
                    $obj->setUpdatedAt(new \DateTime());
                }
            } else {
                if (method_exists($obj, 'setCreatedBy')) {
                    $obj->setCreatedBy($this->getUser());
                }
                if (method_exists($obj, 'setCreatedAt')) {
                    $obj->setCreatedAt(new \DateTime());
                }
            }

            $this->getService()->getEM()->persist($obj);
            $this->getService()->getEM()->flush();

            $this->addFlash('success', 'Der Datensatz wurde erfolgreich gespeichert.');
            return $this->redirectToRoute($this->getService()->getRoutes()->getIndex());
        }

        $params = [
            'form' => $form->createView(),
            'obj' => $obj,
            'routes' => $this->getService()->getRoutes(),
        ];
        if (!is_null($id) && $id > 0) {
            return $this->render($this->getService()->getTemplates()->getEdit(), $params);
        } else {
            return $this->render($this->getService()->getTemplates()->getCreate(), $params);
        }
    }

    /**
     * @param $obj
     * @return object
     */
    protected function prePersist($obj)
    {
        $this->currentObj = $obj;

        return $obj;
    }

    /**
     * @param $obj
     * @return object
     */
    protected function preUpdate($obj)
    {
        $this->currentObj = $obj;

        return $obj;
    }

    /**
     * @param $obj
     * @return object
     */
    protected function preDelete($obj)
    {
        return $obj;
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws ClassNotFoundException
     * @throws \Exception
     */
    public function editAction(Request $request, $id)
    {
        return $this->processForm($request, $id);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws ClassNotFoundException
     * @throws \Exception
     */
    public function createAction(Request $request)
    {
        return $this->processForm($request);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function deleteAction(Request $request, $id)
    {
        $obj = null;
        if (!is_null($id) && $id > 0) {
            $obj = $this->getService()->getOneById($id);
        }
        $form = $this->createFormBuilder()
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $obj = $this->preDelete($obj);

            $this->getService()->getEM()->remove($obj);
            $this->getService()->getEM()->flush();

            return $this->redirectToRoute($this->getService()->getRoutes()->getIndex());
        }

        $params = [
            'routes' => $this->getService()->getRoutes(),
            'obj' => $obj,
            'form' => $form->createView(),
        ];
        return $this->render($this->getService()->getTemplates()->getDelete(), $params);
    }

    /**
     * @param $obj
     * @return mixed
     */
    protected function beforeSubmit($obj)
    {
        return $obj;
    }
}
