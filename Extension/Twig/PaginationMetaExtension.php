<?php

namespace Skuola\SeoBundle\Extension\Twig;

use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\HttpFoundation\Request;

class PaginationMetaExtension extends \Twig_Extension
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var null|Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $domain;

    public function __construct(RequestStack $requestStack, RouterInterface $router, $domain)
    {
        $this->router = $router;
        $this->domain = $domain;
        $this->request = $requestStack->getMasterRequest();
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('render_pagination_meta', [$this, 'renderPaginationMeta'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('render_pagination_canonical', [$this, 'renderPaginationCanonical'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('generate_pagination_robots_meta_tag', [$this, 'renderPaginationRobots'], ['is_safe' => ['html']]),
        ];
    }

    public function renderPaginationRobots($current_page_value)
    {
        $index_value = 'index'; $follow_value = 'follow';

        if($current_page_value > 1){
            $index_value = 'noindex';
        }

        return sprintf('<meta name="robots" content="%s,%s" />', $index_value, $follow_value);
    }

    public function renderPaginationCanonical($request, $query_string = true)
    {
        $canonical_url = $query_string ? $this->generateCanonicalUrl($request) : $request->getPathInfo();

        return sprintf('<link href="%s" rel="canonical">', $canonical_url);
    }

    public function renderPaginationMeta($items)
    {
        if (!$items instanceof SlidingPagination) {
            return;
        }

        $paginationData = $items->getPaginationData();

        $paginationMetas = '';
        $accessor = PropertyAccess::createPropertyAccessor();

        $prev = (int) $accessor->getValue($paginationData, '[previous]');
        $next = (int) $accessor->getValue($paginationData, '[next]');

        if ($prev !== 0) {
            $paginationMetas = $this->generateMeta('prev', $items, $prev);
        }

        if ($next !== 0) {
            $paginationMetas .= $this->generateMeta('next', $items, $next);
        }

        return $paginationMetas;
    }

    protected function generateMeta($direction, $items, $pageNumber)
    {
        $pageUrl = sprintf('http://%s%s', $this->domain, $this->request->getPathInfo());

        $queryString = [];
        parse_str($this->request->getQueryString(), $queryString);

        if ($pageNumber <= 1) {
            if (array_key_exists('page', $queryString)) {
                unset($queryString['page']);
            }

            return sprintf('<link rel="%s" href="%s">',
                $direction,
                $this->buildUrl($queryString, $pageUrl, $items->getPaginatorOptions())
            );
        }

        $queryString['page'] = $pageNumber;

        return sprintf('<link rel="%s" href="%s">',
            $direction,
            $this->buildUrl($queryString, $pageUrl, $items->getPaginatorOptions())
        );
    }

    protected function generateCanonicalUrl(Request $request)
    {
        $route = $request->get('_route');
        $parameters = $request->get('_route_params');

        $current_page = $request->get('page');

        if ($current_page != 1) {
            $parameters['page'] = $current_page;
        }

        return $this->router->generate($route, $parameters, true);
    }

    private function buildUrl(array $queryString, $pageUrl, $paginatorOptions)
    {
        foreach ($queryString as $parameter => $value) {
            if ( ! array_search($parameter, $paginatorOptions)) {
                unset($queryString[$parameter]);
            }
        }

        return (count($queryString) >= 1) ? sprintf('%s?%s', $pageUrl, http_build_query($queryString)) : $pageUrl;
    }

    public function getName()
    {
        return 'twig_pagination_meta_extension';
    }
}
