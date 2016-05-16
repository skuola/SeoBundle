<?php

namespace Skuola\SeoBundle\Tests\Extension;

use Skuola\SeoBundle\Extension\Twig\PaginationMetaExtension;
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

    public function testSeoRobotsForPaginationValues()
    {
        $pagination = new SlidingPagination([]);

        $pagination->setCurrentPageNumber(1);
        $this->assertEquals('<meta name="robots" content="index,follow" />', $this->extension->renderPaginationRobots($pagination));

        $pagination->setCurrentPageNumber(rand(2, 20));
        $this->assertEquals('<meta name="robots" content="noindex,follow" />', $this->extension->renderPaginationRobots($pagination));
    }
}
