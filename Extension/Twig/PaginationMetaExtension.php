<?php

namespace OpenSkuola\SeoBundle\Extension\Twig;

use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class PaginationMetaExtension
 * @package OpenSkuola\SeoBundle\Extension\Twig
 */
class PaginationMetaExtension extends \Twig_Extension
{
    const NEXT = 'next';
    const PREV = 'prev';
    const FIRST_PAGE = 1;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var PropertyAccess
     */
    protected $accessor;

    /**
     * PaginationMetaExtension constructor.
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('render_pagination_meta', [$this, 'renderPaginationMeta'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('render_pagination_robots', [$this, 'renderPaginationRobots'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param SlidingPagination $pagination
     * @return string
     */
    public function renderPaginationRobots(SlidingPagination $pagination)
    {
        $index_value = 'index';
        $follow_value = 'follow';

        if ($pagination->getCurrentPageNumber() > 1) {
            $index_value = 'noindex';
        }

        return sprintf('<meta name="robots" content="%s,%s" />', $index_value, $follow_value);
    }

    /**
     * @param SlidingPagination $pagination
     * @return string
     */
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

    /**
     * @param SlidingPagination $pagination
     * @param $direction
     * @param $page
     * @return string
     */
    protected function generateMeta(SlidingPagination $pagination, $direction, $page)
    {
        $routeInfo['name'] = $pagination->getRoute();

        $routeInfo['params'] = $pagination->getQuery(compact('page'));

        $pageParameterName = $pagination->getPaginatorOption('pageParameterName');

        if (!empty($routeInfo['params'][$pageParameterName])) {
            if ($routeInfo['params'][$pageParameterName] <= self::FIRST_PAGE) {
                unset($routeInfo['params'][$pageParameterName]);
            }
        }

        if ($this->getRequestStack() && $request =  $this->getRequestStack()->getCurrentRequest()) {
            array_map(
                function($parameter) use (&$routeInfo, $request) {
                    if (!$request->query->get($parameter)) {
                        unset($routeInfo['params'][$parameter]);
                    }
                },
                [
                    $pagination->getPaginatorOption('sortFieldParameterName'),
                    $pagination->getPaginatorOption('sortDirectionParameterName')
                ]
            );
        }

        return sprintf('<link rel="%s" href="%s">',
            $direction,
            $this->router->generate(
                $routeInfo['name'],
                $routeInfo['params'],
                UrlGenerator::ABSOLUTE_URL
            )
        );
    }

    /**
     * @return RequestStack
     */
    public function getRequestStack()
    {
        return $this->requestStack;
    }

    /**
     * @param RequestStack $requestStack
     * @return $this
     */
    public function setRequestStack($requestStack)
    {
        $this->requestStack = $requestStack;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'twig_pagination_meta_extension';
    }
}
