<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\DependencyInjection;

use Nelmio\ApiDocBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testDefaultArea()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['areas' => ['path_patterns' => ['/foo']]]]);

        $this->assertSame(['default' => ['path_patterns' => ['/foo'], 'host_patterns' => []]], $config['areas']);
    }

    public function testAreas()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['areas' => $areas = [
            'default' => ['path_patterns' => ['/foo'], 'host_patterns' => []],
            'internal' => ['path_patterns' => ['/internal'], 'host_patterns' => ['^swagger\.']],
            'commercial' => ['path_patterns' => ['/internal'], 'host_patterns' => []],
        ]]]);

        $this->assertSame($areas, $config['areas']);
    }

    public function testAlternativeNames()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [[
                'models' => [
                    'names' => [
                        'Foo1' => [
                            'type' => 'App\Foo',
                            'groups' => ['group'],
                        ],
                        'Foo2' => [
                            'type' => 'App\Foo',
                            'groups' => [],
                        ],
                        'Foo3' => [
                            'type' => 'App\Foo',
                        ],
                        'Foo4' => [
                            'type' => 'App\Foo',
                            'areas' => ['internal'],
                        ],
                    ],
                ],
        ]]);

        $this->assertSame([
            'Foo1' => [
                'type' => 'App\Foo',
                'groups' => ['group'],
                'areas' => ['default'],
            ],
            'Foo2' => [
                'type' => 'App\Foo',
                'groups' => [],
                'areas' => ['default'],
            ],
            'Foo3' => [
                'type' => 'App\Foo',
                'groups' => [],
                'areas' => ['default'],
            ],
            'Foo4' => [
                'type' => 'App\\Foo',
                'areas' => ['internal'],
                'groups' => [],
            ],
        ], $config['models']['names']);
    }

    /**
     * @group legacy
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage You must not use both `nelmio_api_doc.areas` and `nelmio_api_doc.routes` config options. Please update your config to only use `nelmio_api_doc.areas`.
     */
    public function testBothAreasAndRoutes()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['areas' => [], 'routes' => []]]);
    }

    /**
     * @group legacy
     * @expectedDeprecation The `nelmio_api_doc.routes` config option is deprecated. Please use `nelmio_api_doc.areas` instead (just replace `routes` by `areas` in your config).
     */
    public function testDefaultConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['routes' => ['path_patterns' => ['/foo']]]]);

        $this->assertSame(['default' => ['path_patterns' => ['/foo'], 'host_patterns' => []]], $config['areas']);
    }
}
