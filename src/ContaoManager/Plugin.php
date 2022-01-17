<?php

namespace Krabo\IsotopeStockBundle\ContaoManager;

use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;

class Plugin implements BundlePluginInterface, RoutingPluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create('Krabo\IsotopeStockBundle\IsotopeStockBundle')
                ->setLoadAfter(['isotope']),
        ];
    }

  /**
   * Returns a collection of routes for this bundle.
   *
   * @return RouteCollection|null
   */
  public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
  {
    $file = __DIR__.'/../Resources/config/routes.yml';
    return $resolver->resolve($file)->load($file);
  }

}