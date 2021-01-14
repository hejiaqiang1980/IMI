<?php

declare(strict_types=1);

namespace Imi\Swoole\Test\Component\Tests;

use Imi\Pool\PoolManager;
use Imi\Redis\RedisHandler;
use Imi\Redis\RedisManager;
use Imi\Swoole\Test\BaseTest;
use PHPUnit\Framework\Assert;

/**
 * @testdox RedisManager
 */
class RedisManagerTest extends BaseTest
{
    public function testDefaultPoolName()
    {
        Assert::assertEquals('redis_test', RedisManager::getDefaultPoolName());
    }

    public function testNewInstance()
    {
        $pool = PoolManager::getInstance('redis_manager_test');
        $instance = null;
        try
        {
            Assert::assertEquals(1, $pool->getCount());
            Assert::assertEquals(1, $pool->getFree());

            $instance = RedisManager::getNewInstance('redis_manager_test');

            Assert::assertEquals(1, $pool->getCount());
            Assert::assertEquals(0, $pool->getFree());
            $this->assertRedisHandler($instance);
        }
        finally
        {
            if ($instance)
            {
                RedisManager::release($instance);
                Assert::assertEquals(1, $pool->getCount());
                Assert::assertEquals(1, $pool->getFree());
            }
        }
    }

    public function testInstance()
    {
        $pool = PoolManager::getInstance('redis_manager_test');
        $this->go(function () use ($pool) {
            Assert::assertEquals(1, $pool->getCount());
            Assert::assertEquals(1, $pool->getFree());

            $instance = RedisManager::getInstance('redis_manager_test');

            Assert::assertEquals(1, $pool->getCount());
            Assert::assertEquals(0, $pool->getFree());

            $this->assertRedisHandler($instance);
        });
        sleep(1);
        Assert::assertEquals(1, $pool->getCount());
        Assert::assertEquals(1, $pool->getFree());
    }

    /**
     * @param \Imi\Redis\RedisHandler $redisHandler
     *
     * @return void
     */
    private function assertRedisHandler($redisHandler)
    {
        Assert::assertInstanceOf(RedisHandler::class, $redisHandler);
        $time = time();
        $redisHandler->set('imi:test:a', $time);
        Assert::assertEquals($time, $redisHandler->get('imi:test:a'));
    }
}
