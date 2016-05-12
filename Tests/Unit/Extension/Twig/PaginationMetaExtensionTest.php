<?php

namespace Skuola\SeoBundle\Tests\Extension;

use Skuola\SeoBundle\Extension\Twig\PaginationMetaExtension;
use Symfony\Component\Routing\Router;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Mockery as m;

class PaginationMetaExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected $routerMock;
    protected $requestStackMock;
    protected $requestMock;

    public function setUp()
    {
        $this->routerMock = $this->getMock(Router::class, ['generate'], [], '', false);

        $this->routerMock->expects($this->any())
            ->method('generate')
            ->with($this->equalTo('dummy_route'))
            ->will($this->returnValue('/test/path-info'));

        $this->requestStackMock = $this->getMock(RequestStack::class, ['getMasterRequest'], [], '', false);
    }

    public function tearDown()
    {
        m::close();
    }

    public function testExtensionGiveNullIfNoPages()
    {
        $slidingPaginationMock = $this->getMock(SlidingPagination::class, array('getPaginationData'), [], '', false);

        $slidingPaginationMock->expects($this->any())
            ->method('getPaginationData')
            ->will($this->returnValue([]));

        $requestMock = $this->getMock(Request::class, ['getPathInfo'], [], '', true);

        $requestMock->expects($this->any())
            ->method('getPathInfo')
            ->will($this->returnValue('/test/path-info'));

        $this->requestStackMock->expects($this->any())
            ->method('getMasterRequest')
            ->will($this->returnValue($requestMock));

        $paginationMeta = new PaginationMetaExtension($this->requestStackMock, $this->routerMock, 'local.domain.it');

        $this->assertEmpty($paginationMeta->renderPaginationMeta($slidingPaginationMock));
    }

    public function testExtensionGiveNextIfFirstPage()
    {
        $slidingPaginationMock = $this->getMock('Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination', array('getPaginationData', 'getParams', 'getRoute', 'getPaginatorOptions'), [], '', false);

        $slidingPaginationMock->expects($this->at(0))
            ->method('getPaginationData')
            ->will($this->returnValue(array('next' => 2)));

        $slidingPaginationMock->expects($this->any())
            ->method('getRoute')
            ->will($this->returnValue('dummy_route'));

        $slidingPaginationMock->expects($this->any())
            ->method('getPaginatorOptions')
            ->will($this->returnValue([
                'pageParameterName' => 'page',
                'sortDirectionParameterName' => 'direction',
            ]));

        $requestMock = $this->getMock(
            'Symfony\Component\HttpFoundation\Request',
            ['getPathInfo'],
            [
                [
                    'testParameter' => 'test',
                    'page'          => 1,
                    'direction'     => 'desc'
                ]
            ],
            '',
            true
        );

        $requestMock->expects($this->any())
            ->method('getPathInfo')
            ->will($this->returnValue('/test/path-info'));

        $this->requestStackMock->expects($this->any())
            ->method('getMasterRequest')
            ->will($this->returnValue($requestMock));

        $paginationMeta = new PaginationMetaExtension($this->requestStackMock, $this->routerMock, 'local.domain.it');

        $this->assertEquals('<link rel="next" href="http://local.domain.it/test/path-info?page=2">', $paginationMeta->renderPaginationMeta($slidingPaginationMock));
    }

    public function testExtensionGivePrevIfLastPage()
    {
        $slidingPaginationMock = $this->getMock('Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination', array('getPaginationData', 'getParams', 'getRoute', 'getPaginatorOptions'), [], '', false);

        $slidingPaginationMock->expects($this->at(0))
            ->method('getPaginationData')
            ->will($this->returnValue(array('previous' => 1)));

        $slidingPaginationMock->expects($this->any())
            ->method('getRoute')
            ->will($this->returnValue('dummy_route'));

        $slidingPaginationMock->expects($this->any())
            ->method('getPaginatorOptions')
            ->will($this->returnValue([
                'pageParameterName' => 'page'
            ]));

        $requestMock = $this->getMock(
            'Symfony\Component\HttpFoundation\Request',
            ['getPathInfo'],
            [
                [
                    'testParameter' => 'test',
                    'page'          => 2
                ]
            ],
            '',
            true
        );

        $requestMock->expects($this->any())
            ->method('getPathInfo')
            ->will($this->returnValue('/test/path-info'));

        $this->requestStackMock->expects($this->any())
            ->method('getMasterRequest')
            ->will($this->returnValue($requestMock));

        $paginationMeta = new PaginationMetaExtension($this->requestStackMock, $this->routerMock, 'local.domain.it');

        $this->assertEquals('<link rel="prev" href="http://local.domain.it/test/path-info">', $paginationMeta->renderPaginationMeta($slidingPaginationMock));
    }

    public function testExtensionGivePrevAndNextInBetweenPages()
    {
        $slidingPaginationMock = $this->getMock('Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination', ['getPaginationData', 'getParams', 'getRoute', 'getPaginatorOptions'], [], '', false);

        $slidingPaginationMock->expects($this->at(0))
            ->method('getPaginationData')
            ->will(
                $this->returnValue([
                        'previous' => 1,
                        'next' => 3
                    ]
                ));

        $slidingPaginationMock->expects($this->any())
            ->method('getRoute')
            ->will($this->returnValue('dummy_route'));

        $slidingPaginationMock->expects($this->any())
            ->method('getParams')
            ->will($this->returnValue(array('direction' => 'desc')));

        $slidingPaginationMock->expects($this->any())
            ->method('getPaginatorOptions')
            ->will($this->returnValue([
                'pageParameterName' => 'page'
            ]));

        $requestMock = $this->getMock(
            'Symfony\Component\HttpFoundation\Request',
            ['getPathInfo'],
            [
                [
                    'testParameter' => 'test',
                    'page'          => 2
                ]
            ],
            '',
            true
        );

        $requestMock->expects($this->any())
            ->method('getPathInfo')
            ->will($this->returnValue('/test/path-info'));

        $this->requestStackMock->expects($this->any())
            ->method('getMasterRequest')
            ->will($this->returnValue($requestMock));

        $paginationMeta = new PaginationMetaExtension($this->requestStackMock, $this->routerMock, 'local.domain.it');

        $this->assertEquals('<link rel="prev" href="http://local.domain.it/test/path-info"><link rel="next" href="http://local.domain.it/test/path-info?page=3">', $paginationMeta->renderPaginationMeta($slidingPaginationMock));
    }

    public function testSeoRobotsForPaginationValues()
    {
        $page_parameters = array(
            'first_page' => 1,
            'random_page' => rand(2,20),
        );

        $paginationMeta = new PaginationMetaExtension($this->requestStackMock, $this->routerMock,'local.domain.it');

        $this->assertEquals('<meta name="robots" content="index,follow" />', $paginationMeta->renderPaginationRobots($page_parameters['first_page']));
        $this->assertEquals('<meta name="robots" content="noindex,follow" />', $paginationMeta->renderPaginationRobots($page_parameters['random_page']));
    }

    public function testCanonicalTag()
    {
        $router = m::mock('Symfony\Bundle\FrameworkBundle\Routing\Router');
        $router->shouldReceive('generate')
            ->with('my_route', ['hello' => 'world', 'page' => 2], true)
            ->andReturn('http://testing/canonical');

        $extension = new PaginationMetaExtension($this->requestStackMock, $router, 'test.skuola.net');

        $request = new Request(['page' => 2, 'other' => 'parameter'], [], [
            '_route' => 'my_route',
            '_route_params' => ['hello' => 'world'],
        ]);

        $this->assertEquals('<link href="http://testing/canonical" rel="canonical">', $extension->renderPaginationCanonical($request));
    }

    public function testFirstPageCanonicalTag()
    {
        $router = m::mock('Symfony\Bundle\FrameworkBundle\Routing\Router');
        $router->shouldReceive('generate')
            ->with('my_route', ['hello' => 'world'], true)
            ->andReturn('http://testing/canonical');

        $extension = new PaginationMetaExtension($this->requestStackMock, $router, 'test.skuola.net');

        $request = new Request(['page' => 1, 'other' => 'parameter'], [], [
            '_route' => 'my_route',
            '_route_params' => ['hello' => 'world'],
        ]);

        $this->assertContains('href="http://testing/canonical" rel="canonical">', $extension->renderPaginationCanonical($request));
    }
}
