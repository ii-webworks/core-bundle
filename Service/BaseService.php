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
use webworks\CoreBundle\Model\Routes;
use webworks\CoreBundle\Model\RoutesInterface;
use webworks\CoreBundle\Model\Templates;
use webworks\CoreBundle\Model\TemplatesInterface;

/**
 * Class BaseService
 * @package webworks\BackendBundle\Service
 */
abstract class BaseService
{
    const TABLE_ALIAS = 'a';

    private $container;
    /** @var RoutesInterface $routes */
    private $routes;
    /** @var TemplatesInterface $templates */
    private $templates;

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
     * @param string $prefix
     * @return RoutesInterface
     * @throws \Exception
     */
    public function getRoutes($prefix = '')
    {
        if (!$this->routes instanceof RoutesInterface) {
            $routes = $this->configureRoutes($prefix);
            if (!$routes instanceof RoutesInterface) {
                throw new \Exception('The method getRoutesConfig() must return a RoutesInterface Object');
            }
            $this->setRoutes($routes);
        }

        return $this->routes;
    }

    /**
     * @param RoutesInterface $routes
     * @return $this
     * @throws \Exception
     */
    public function setRoutes(RoutesInterface $routes)
    {
        $this->validateRoutes($routes);
        $this->routes = $routes;

        return $this;
    }

    /**
     * @param RoutesInterface $routes
     * @return bool
     * @throws \Exception
     */
    public function validateRoutes(RoutesInterface $routes)
    {
        $router = $this->getContainer()->get('router');
        foreach ($routes->getRouteKeys() as $keyname) {
            $getter = 'get' . $keyname;
            if (!method_exists($routes, $getter)) {
                throw new \Exception('Method "' . $getter . '" for property "' . $keyname . '" in class "' . get_class($routes) . '" not found. Please define a getter and setter for your properties.');
            }
            if (is_null($router->getRouteCollection()->get($routes->$getter()))) {
                throw new \Exception('Unknown route "' . $routes->$getter() . '".');
            }
        }
        return true;
    }

    /**
     * @param string $prefix
     * @return TemplatesInterface
     * @throws \Exception
     */
    public function getTemplates($prefix = '')
    {
        if (!$this->templates instanceof TemplatesInterface) {
            $templates = $this->configureTemplates($prefix);
            if (!$templates instanceof TemplatesInterface) {
                throw new \Exception('The method getTemplatesConfig() has to return a TemplatesInterface Object');
            }
            $this->setTemplates($templates);
        }
        return $this->templates;
    }

    /**
     * @param TemplatesInterface $templates
     * @return $this
     * @throws \Exception
     */
    public function setTemplates(TemplatesInterface $templates)
    {
        $this->validateTemplates($templates);
        $this->templates = $templates;

        return $this;
    }

    /**
     * @param TemplatesInterface $templates
     * @return bool
     * @throws \Exception
     */
    public function validateTemplates(TemplatesInterface $templates)
    {
        $templateEngine = $this->getContainer()->get('templating');
        foreach ($templates->getRouteKeys() as $keyname) {
            $getter = 'get' . $keyname;
            if (!method_exists($templates, $getter)) {
                throw new \Exception('Method "' . $getter . '" for property "' . $keyname . '" in class "' . get_class($templates) . '" not found. Please define a getter and setter for your properties.');
            }
            if (!$templateEngine->exists($templates->$getter())) {
                throw new \Exception('Unknown template "' . $templates->$getter() . '".');
            }
        }

        return true;
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

    public function getTableAlias()
    {
        return self::TABLE_ALIAS;
    }


    /**
     * @param $prefix
     * @return Templates
     */
    public function configureTemplates($prefix)
    {
        $templates = new Templates($prefix);

        return $templates
            ->setIndex('index.html.twig')
            ->setCreate('create.html.twig')
            ->setEdit('edit.html.twig')
            ->setDelete('delete.html.twig');
    }

    /**
     * @param $prefix
     * @return Routes
     */
    public function configureRoutes($prefix)
    {
        $routes = new Routes($prefix);

        return $routes
            ->setIndex('index')
            ->setCreate('create')
            ->setEdit('edit')
            ->setDelete('delete');
    }
}
