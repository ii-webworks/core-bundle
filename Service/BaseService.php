<?php
/**
 * Created by PhpStorm.
 * User: adachauer
 * Date: 18.02.18
 * Time: 15:22
 */

namespace webworks\CoreBundle\Service;

use Doctrine\ORM\Query;
use FOS\UserBundle\Model\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BaseService
 * @package webworks\BackendBundle\Service
 */
abstract class BaseService
{
    private $container;
    private $routePrefix = '';
    private $templatePrefix = '';
    private $tableAlias = 'a';
    private $routes = [
        'index' => 'index',
        'edit' => 'edit',
        'create' => 'create',
        'delete' => 'delete',
    ];
    private $templates = [
        'index' => 'index.html.twig',
        'edit' => 'edit.html.twig',
        'create' => 'create.html.twig',
        'delete' => 'delete.html.twig',
    ];

    /**
     * BaseService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    abstract public function getFormClassName();

    /**
     * @return string
     */
    public abstract function getEntityClassName();

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * @return \Doctrine\ORM\EntityManager|object
     */
    public function getEM()
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->getContainer()->get('security.token_storage')->getToken()->getUser();
    }


    /**
     * @param $obj
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($obj)
    {
        return $this
            ->getContainer()
            ->get('form.factory')
            ->create($this->getFormClassName(), $obj);
    }

    /**
     * @param $id
     * @return null|object
     */
    public function getOneById($id)
    {
        return $this->getEM()->getRepository($this->getEntityClassName())
            ->find($id);
    }

    /**
     * @return Query
     */
    public function getAll()
    {
        return $this->getEM()->getRepository($this->getEntityClassName())
            ->createQueryBuilder($this->getTableAlias())
            ->getQuery();
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return $this->tableAlias;
    }

    /**
     * @param $alias
     */
    protected function setTableAlias($alias)
    {
        $this->tableAlias = $alias;
    }

    /**
     * @param $prefix
     */
    protected function setTemplatePrefix($prefix)
    {
        $this->templatePrefix = $prefix;
    }

    /**
     * @param $prefix
     */
    protected function setRoutePrefix($prefix)
    {
        $this->routePrefix = $prefix;
    }

    /**
     * @param array $templates
     */
    protected function setTemplates(array $templates)
    {
        $this->templates = $templates;
    }

    /**
     * @param array $routes
     */
    protected function setRoutes(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * @param $path
     * @return bool
     */
    private function templateExists($path)
    {
        $templating = $this->getContainer()->get('templating');

        return $templating->exists($path);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getTemplates()
    {
        $templates = [];

        if (sizeof($this->templates) > 0) {
            foreach ($this->templates as $key => $template) {
                if ($this->templateExists($template)) {
                    $templates[$key] = $template;
                } else {
                    $prefixedTemplate = $this->templatePrefix . $template;
                    if ($this->templateExists($prefixedTemplate)) {
                        $templates[$key] = $prefixedTemplate;
                    } else {
                        throw new \Exception('Template "' . $template . '" not found with prefix "' . $this->templatePrefix . '".');
                    }
                }
            }
        }

        return $templates;
    }

    /**
     * @param $key
     * @return mixed
     * @throws \Exception
     */
    public function getTemplate($key)
    {
        $templates = $this->getTemplates();

        if (isset($templates[$key])) {
            return $templates[$key];
        }

        throw new \Exception('Template for key"' . $key . '" not found.');
    }

    private function routeExists($name)
    {
        $router = $this->container->get('router');
        return (null === $router->getRouteCollection()->get($name)) ? false : true;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getRoutes()
    {
        $routes = [];

        if (sizeof($this->routes) > 0) {
            foreach ($this->routes as $key => $route) {
                if ($this->routeExists($route)) {
                    $routes[$key] = $route;
                } else {
                    $prefixedRoute = $this->routePrefix . $route;
                    if ($this->routeExists($prefixedRoute)) {
                        $routes[$key] = $prefixedRoute;
                    } else {
                        throw new \Exception('Route "' . $route . '" not found with prefix "' . $this->routePrefix . '".');
                    }
                }
            }
        }

        return $routes;
    }

    /**
     * @param $key
     * @return mixed
     * @throws \Exception
     */
    public function getRoute($key)
    {
        $routes = $this->getRoutes();

        if (isset($routes[$key])) {
            return $routes[$key];
        }

        throw new \Exception('Route for key"' . $key . '" not found.');
    }
}
