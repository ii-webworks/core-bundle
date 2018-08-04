<?php
/**
 * Created by PhpStorm.
 * User: adachauer
 * Date: 20.02.18
 * Time: 14:53
 */

namespace webworks\CoreBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use webworks\CoreBundle\Helper\StringHelper;

class TwigExtension extends \Twig_Extension
{

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('sanitize', array($this, 'sanitize')),
        );
    }

    public function sanitize($string)
    {
        $helper = new StringHelper();
        return $helper->sanitizeString($string);
    }
}