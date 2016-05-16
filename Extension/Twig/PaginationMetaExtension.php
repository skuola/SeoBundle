<?php

namespace Skuola\SeoBundle\Extension\Twig;

use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class PaginationMetaExtension extends \Twig_Extension
{
    const NEXT = 'next';
    const PREV = 'prev';

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var PropertyAccess
     */
    protected $accessor;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('render_pagination_meta', [$this, 'renderPaginationMeta'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('render_pagination_robots', [$this, 'renderPaginationRobots'], ['is_safe' => ['html']]),
        ];
    }

    public function renderPaginationRobots(SlidingPagination $pagination)
    {
        $index_value = 'index';
        $follow_value = 'follow';

        if($pagination->getCurrentPageNumber() > 1){
            $index_value = 'noindex';
        }

        return sprintf('<meta name="robots" content="%s,%s" />', $index_value, $follow_value);
    }

    public function renderPaginationMeta(SlidingPagination $pagination)
    {
        $paginationData = $pagination->getPaginationData();
        $paginationMetas = '';

        $prev = (int) $this->accessor->getValue($paginationData, '[previous]');
        $next = (int) $this->accessor->getValue($paginationData, '[next]');

        if ($prev !== 0) {
            $paginationMetas = $this->generateMeta($pagination, self::PREV, $prev);
        }

        if ($next !== 0) {
            $paginationMetas .= $this->generateMeta($pagination, self::NEXT, $next);
        }

        return $paginationMetas;
    }

    protected function generateMeta(SlidingPagination $pagination, $direction, $page)
    {
        $routeInfo['name'] = $pagination->getRoute();

        $routeInfo['params'] = $pagination->getQuery(compact('page'));

        return sprintf('<link rel="%s" href="%s">',
            $direction,
            $this->router->generate(
                $routeInfo['name'],
                $routeInfo['params'],
                UrlGenerator::ABSOLUTE_URL
            )
        );
    }

    public function getName()
    {
        return 'twig_pagination_meta_extension';
    }
}
