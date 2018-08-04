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
use Symfony\Component\Form\Form;

/**
 * Class BaseService
 * @package webworks\BackendBundle\Service
 */
abstract class BaseService
{

    private $container;

    /**
     * BaseService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

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

    public abstract function getClassName();

    /**
     * @return Query
     */
    abstract public function getAll();

    /**
     * @return array
     */
    abstract public function getTemplates();

    /**
     * @param $key
     * @return string
     * @throws \Exception
     */
    public function getTemplate($key)
    {
        $templates = $this->getTemplates();
        if (isset($templates[$key])) {
            return $templates[$key];
        }

        throw new \Exception('Template for key "' . $key . '" not found.');
    }

    /**
     * @return Form
     */
    abstract public function getForm($obj);

    /**
     * @param $id
     * @return object
     */
    abstract public function getOneById($id);

    /**
     * @return array
     */
    abstract public function getRoutes();

    /**
     * @param $key
     * @return string
     * @throws \Exception
     */
    public function getRoute($key)
    {
        $routes = $this->getRoutes();
        if (isset($routes[$key])) {
            return $routes[$key];
        }

        throw new \Exception('Route for key "' . $key . '" not found.');
    }
}
