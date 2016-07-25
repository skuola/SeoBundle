<?php

namespace OpenSkuola\SeoBundle\Tests\Extension;

use OpenSkuola\SeoBundle\Extension\Twig\PaginationMetaExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Mockery as m;

class PaginationMetaExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var PaginationMetaExtension
     */
    protected $extension;

    public function setUp()
    {
        $this->router = m::mock(Router::class);
        $this->extension = new PaginationMetaExtension($this->router);
    }

    public function tearDown()
    {
        m::close();
    }

    public function testExtensionGiveNullIfNoPages()
    {
        $pagination = m::mock(SlidingPagination::class);

        $pagination->shouldReceive('getPaginationData')
            ->once()
            ->andReturn([
                'previous' => '0',
                'next'     => '0'
            ]);

        $this->assertEmpty($this->extension->renderPaginationMeta($pagination));
    }

    public function testExtensionGiveNextIfFirstPage()
    {
        $pagination = m::mock(SlidingPagination::class);

        $pagination->shouldReceive('getPaginationData')
            ->once()
            ->andReturn([
                'next'     => '2'
            ]);

        $pagination->shouldReceive('getRoute')
            ->once()
            ->andReturn('dummy_route');

        $pagination->shouldReceive('getQuery')
            ->once()
            ->andReturn([
                'direction' => 'asc',
                'sort'      => 'sorting'
            ]);

        $pagination->shouldReceive('getPaginatorOption')
            ->with('pageParameterName')
            ->andReturn('page');

        $this->router->shouldReceive('generate')
            ->once()
            ->andReturn('http://local.domain.it/test/path-info?page=2');

        $this->assertEquals(
            '<link rel="next" href="http://local.domain.it/test/path-info?page=2">',
            $this->extension->renderPaginationMeta($pagination)
        );
    }

    public function testExtensionGivePrevIfLastPage()
    {
        $pagination = m::mock(SlidingPagination::class);

        $pagination->shouldReceive('getPaginationData')
            ->once()
            ->andReturn([
                'previous' => '9',
                'next'     => '0'
            ]);

        $pagination->shouldReceive('getRoute')
            ->once()
            ->andReturn('dummy_route');

        $pagination->shouldReceive('getQuery')
            ->once()
            ->andReturn([
                'direction' => 'asc',
                'sort'      => 'sorting'
            ]);

        $pagination->shouldReceive('getPaginatorOption')
            ->with('pageParameterName')
            ->andReturn('page');

        $this->router->shouldReceive('generate')
            ->once()
            ->andReturn('http://local.domain.it/test/path-info?page=9');

        $this->assertEquals(
            '<link rel="prev" href="http://local.domain.it/test/path-info?page=9">',
            $this->extension->renderPaginationMeta($pagination)
        );
    }

    public function testExtensionGivePrevAndNextInBetweenPages()
    {
        $pagination = m::mock(SlidingPagination::class);

        $pagination->shouldReceive('getPaginationData')
            ->once()
            ->andReturn([
                'previous' => '7',
                'next'     => '9'
            ]);

        $pagination->shouldReceive('getRoute')
            ->twice()
            ->andReturn('dummy_route');

        $pagination->shouldReceive('getQuery')
            ->twice()
            ->andReturn([
                'direction' => 'asc',
                'sort'      => 'sorting'
            ]);

        $pagination->shouldReceive('getPaginatorOption')
            ->with('pageParameterName')
            ->andReturn('page');

        $this->router->shouldReceive('generate')
            ->twice()
            ->andReturn(
                'http://local.domain.it/test/path-info?page=7',
                'http://local.domain.it/test/path-info?page=9'
            );

        $this->assertEquals(
            '<link rel="prev" href="http://local.domain.it/test/path-info?page=7"><link rel="next" href="http://local.domain.it/test/path-info?page=9">',
            $this->extension->renderPaginationMeta($pagination)
        );
    }

    public function testExtensionGivePrevAndNextInBetweenPagesWithRequestStack()
    {
        $pagination = m::mock(SlidingPagination::class);

        $pagination->shouldReceive('getPaginationData')
            ->once()
            ->andReturn([
                'previous' => '7',
                'next'     => '9'
            ]);

        $pagination->shouldReceive('getRoute')
            ->twice()
            ->andReturn('dummy_route');

        $pagination->shouldReceive('getQuery')
            ->twice()
            ->andReturn([
                'direction' => 'asc',
                'sort'      => 'sorting'
            ]);

        $pagination->shouldReceive('getPaginatorOption')
            ->with('pageParameterName')
            ->andReturn('page');

        $pagination->shouldReceive('getPaginatorOption')
            ->with('sortFieldParameterName')
            ->andReturn('sort');

        $pagination->shouldReceive('getPaginatorOption')
            ->with('sortDirectionParameterName')
            ->andReturn('direction');

        $request = new Request();
        $request->query->set('sort', null);
        $request->query->set('direction', null);

        $requestStack = m::mock(RequestStack::class);
        $requestStack->shouldReceive('getCurrentRequest')
            ->andReturn($request);

        $this->extension->setRequestStack($requestStack);

        $this->router->shouldReceive('generate')
            ->twice()
            ->withArgs(
                function ($name, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH) {
                    return (
                        $name === 'dummy_route' &&
                        $parameters === [] &&
                        $referenceType == UrlGeneratorInterface::ABSOLUTE_URL
                    );
                }
            )
            ->andReturn(
                'http://local.domain.it/test/path-info?page=7',
                'http://local.domain.it/test/path-info?page=9'
            );
        ;

        $this->assertEquals(
            '<link rel="prev" href="http://local.domain.it/test/path-info?page=7"><link rel="next" href="http://local.domain.it/test/path-info?page=9">',
            $this->extension->renderPaginationMeta($pagination)
        );
    }

    public function testSeoRobotsForPaginationValues()
    {
        $pagination = new SlidingPagination([]);

        $pagination->setCurrentPageNumber(1);
        $this->assertEquals('<meta name="robots" content="index,follow" />', $this->extension->renderPaginationRobots($pagination));

        $pagination->setCurrentPageNumber(rand(2, 20));
        $this->assertEquals('<meta name="robots" content="noindex,follow" />', $this->extension->renderPaginationRobots($pagination));
    }
}
