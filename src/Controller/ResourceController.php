<?php

namespace Jma\ResourceBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\ResourceController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;

class ResourceController extends BaseController
{

    /**
     * Get collection (paginated by default) of resources.
     */
    public function indexAction(Request $request)
    {
        $config = $this->getConfiguration();

        $criteria = $config->getCriteria();
        $sorting = $config->getSorting();

        $pluralName = $config->getPluralResourceName();
        $repository = $this->getRepository();

        if ($config->isPaginated()) {
            $resources = $this
                    ->resourceResolver
                    ->getResource($repository, 'createPaginator', array($criteria, $sorting))
            ;

            $resources
                    ->setCurrentPage($request->get('page', 1), true, true)
                    ->setMaxPerPage($config->getPaginationMaxPerPage())
            ;
        } else {
            $resources = $this
                    ->resourceResolver
                    ->getResource($repository, 'findBy', array($criteria, $sorting, $config->getLimit()))
            ;
        }

        if ($config->isApiRequest()) {
            return $this->handleView($this->view($resources));
        }

        $templateData = array(
            $pluralName => $resources
        );

        $view = $this
                ->view()
                ->setTemplate($config->getTemplate('index.html'))
                ->setTemplateVar($pluralName)
                ->setData($this->indexTemplateExtraData($request, $templateData));

        return $this->handleView($view);
    }

    /**
     * Get collection paginated and filterable of resources.
     */
    public function indexFilterableAction(Request $request)
    {
        if ($request->query->has('reinit') === true) {
            $this->removeFormFilterDataInSession();
        }

        $config = $this->getConfiguration();
        $pluralName = $config->getPluralResourceName();

        $formFilter = $this->getFormFilter();

        $resources = $this->getResourcesWithFormFilter($formFilter, $request);
        $resources
                ->setCurrentPage($request->get('page', 1), true, true)
                ->setMaxPerPage($config->getPaginationMaxPerPage());

        if ($config->isApiRequest()) {
            return $this->handleView($this->view($resources));
        }

        $templateData = array(
            $pluralName => $resources,
            'formFilter' => $formFilter->createView()
        );

        $view = $this
                ->view()
                ->setTemplate($config->getTemplate('index.html'))
                ->setTemplateVar($pluralName)
                ->setData($this->indexFilterableTemplateExtraData($request, $templateData));

        return $this->handleView($view);
    }

    /**
     * Display the form for editing or update the resource.
     */
    public function updateAction(Request $request)
    {
        $resource = $this->findOr404($request);
        $form = $this->getForm($resource);

        if (($request->isMethod('PUT') || $request->isMethod('POST')) && $form->submit($request)->isValid()) {
            $this->domainManager->update($resource);

            return $this->redirectHandler->redirectTo($resource);
        }

        if ($this->config->isApiRequest()) {
            return $this->handleView($this->view($form));
        }

        $templateData = array(
            $this->config->getResourceName() => $resource,
            'form' => $form->createView()
        );

        $view = $this
                ->view()
                ->setTemplate($this->getConfiguration()->getTemplate('update.html'))
                ->setData($this->updateTemplateExtraData($request, $templateData))
        ;

        return $this->handleView($view);
    }

    //<editor-fold defaultstate="collapsed" desc="Template Extra Data">
    protected function indexTemplateExtraData(Request $request, $data)
    {
        return $data;
    }

    protected function indexFilterableTemplateExtraData(Request $request, $data)
    {
        return $data;
    }

    protected function updateTemplateExtraData(Request $request, $data)
    {
        return $data;
    }

    //</editor-fold>
    //<editor-fold defaultstate="collapsed" desc="FormFilter">
    protected function bindFormFilter($formFilter, $data, $criteria, $sorting)
    {
        if ($data === null) {
            return $this->getRepository()->createPaginator($criteria, $sorting);
        }

        $formFilter->submit($data);
        if ($formFilter->isValid()) {
            $this->setFormFilterDataInSession($data);

            $updater = $this->get('lexik_form_filter.query_builder_updater');

            return $this->getRepository()->createPaginatorWithFilters($updater
                            , $formFilter
                            , $criteria
                            , $sorting);
        } else {
            return $this->getRepository()->createPaginator($criteria, $sorting);
        }
    }

    protected function getResourcesWithFormFilter($formFilter, Request $request)
    {
        $config = $this->getConfiguration();

        $criteria = $config->getCriteria();
        $sorting = $config->getSorting();

        if ($request->isMethod('GET')) {
            return $this->bindFormFilter($formFilter, $this->getFormFilterDataInSession(), $criteria, $sorting);
        } else {
            return $this->bindFormFilter($formFilter, $request, $criteria, $sorting);
        }
    }

    protected function getKeyFormFilterData()
    {
        $config = $this->getConfiguration();
        $key = implode('.', array($config->getBundlePrefix(), $config->getResourceName(), 'form_filter_data'));

        return $key;
    }

    protected function setFormFilterDataInSession($formFilterData)
    {
        $this->get('session')->set($this->getKeyFormFilterData(), $formFilterData);
    }

    protected function getFormFilterDataInSession()
    {        
        return $this->get('session')->get($this->getKeyFormFilterData());
    }

    protected function hasFormFilterDataInSession()
    {
        return $this->get('session')->has($this->getKeyFormFilterData());
    }

    protected function removeFormFilterDataInSession()
    {
        $this->get('session')->remove($this->getKeyFormFilterData());
    }

    /**
     * @return FormInterface
     */
    public function getFormFilter()
    {
        return $this->createForm($this->getConfiguration()->getFilterType());
    }

    //</editor-fold>
    //<editor-fold defaultstate="collapsed" desc="Trans">
    protected function trans($message, $params = array(), $domain = null)
    {
        return $this->get('translator')->trans($message, $params, $domain);
    }

    //</editor-fold>
}
