<?php

declare(strict_types=1);

namespace Imi\Swoole\Server\UdpServer\Middleware;

use Imi\Bean\Annotation\Bean;
use Imi\RequestContext;
use Imi\Server\Annotation\ServerInject;
use Imi\Swoole\Server\UdpServer\Error\IUdpRouteNotFoundHandler;
use Imi\Swoole\Server\UdpServer\IPacketHandler;
use Imi\Swoole\Server\UdpServer\Message\IPacketData;
use Imi\Swoole\Server\UdpServer\Route\UdpRoute;

/**
 * @Bean("UDPRouteMiddleware")
 */
class RouteMiddleware implements IMiddleware
{
    /**
     * @ServerInject("UdpRoute")
     *
     * @var \Imi\Swoole\Server\UdpServer\Route\UdpRoute
     */
    protected UdpRoute $route;

    /**
     * @ServerInject("UdpRouteNotFoundHandler")
     *
     * @var \Imi\Swoole\Server\UdpServer\Error\IUdpRouteNotFoundHandler
     */
    protected IUdpRouteNotFoundHandler $notFoundHandler;

    /**
     * 处理方法.
     *
     * @param IReceiveData    $data
     * @param IReceiveHandler $handle
     *
     * @return void
     */
    public function process(IPacketData $data, IPacketHandler $handler)
    {
        // 路由解析
        $result = $this->route->parse($data->getFormatData());
        if (null === $result || !\is_callable($result->callable))
        {
            // 未匹配到路由
            $result = $this->notFoundHandler->handle($data, $handler);
        }
        else
        {
            RequestContext::set('routeResult', $result);
            $result = $handler->handle($data);
        }

        return $result;
    }
}