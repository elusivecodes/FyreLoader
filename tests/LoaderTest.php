<?php
declare(strict_types=1);

namespace Tests;

use
    Fyre\Loader\Loader,
    Fyre\Utility\Path,
    PHPUnit\Framework\TestCase;

final class LoaderTest extends TestCase
{

    public function testClassMap(): void
    {
        Loader::addClassMap([
            'Test' => 'tests/classes/test.php'
        ]);

        $this->assertEquals(
            true,
            \Test::test()
        );
    }

    public function testNamespace(): void
    {
        Loader::addNamespaces([
            'Demo' => 'tests/classes/Demo'
        ]);

        $this->assertEquals(
            true,
            \Demo\Test::test()
        );
    }

    public function testNamespaceTrailingSlash(): void
    {
        Loader::addNamespaces([
            'Demo' => 'tests/classes/Demo/'
        ]);

        $this->assertEquals(
            true,
            \Demo\Test::test()
        );
    }

    public function testNamespaceArray(): void
    {
        Loader::addNamespaces([
            'Demo' => [
                'tests/classes/Demo'
            ]
        ]);

        $this->assertEquals(
            true,
            \Demo\Test::test()
        );
    }

    public function testNamespaceDeep(): void
    {
        Loader::addNamespaces([
            'Demo' => 'tests/classes/Demo'
        ]);

        $this->assertEquals(
            true,
            \Demo\Deep\Test::test()
        );
    }

    public function testLoadComposer(): void
    {
        Loader::loadComposer('tests/Mock/autoload.php');

        $this->assertEquals(
            [
                Path::resolve('src')
            ],
            Loader::getNamespace('Fyre')
        );
    }

    public function testGetNamespace(): void
    {
        Loader::addNamespaces([
            'Test' => 'tests/',
            'Demo' => 'tests/classes/Demo'
        ]);

        $this->assertEquals(
            [
                Path::resolve('tests/classes/Demo')
            ],
            Loader::getNamespace('Demo')
        );
    }

    public function testGetNamespaceInvalid(): void
    {
        $this->assertEquals(
            [],
            Loader::getNamespace('Demo')
        );
    }

    public function testRemoveNamespace(): void
    {
        Loader::addNamespaces([
            'Demo' => 'tests/classes/Demo'
        ]);

        Loader::removeNamespace('Demo');

        $this->assertEquals(
            [],
            Loader::getNamespace('Demo')
        );
    }

    public function testRemoveNamespaceInvalid(): void
    {
        Loader::removeNamespace('Demo');

        $this->assertEquals(
            [],
            Loader::getNamespace('Demo')
        );
    }

    protected function setUp(): void
    {
        Loader::register();
    }

    protected function tearDown(): void
    {
        Loader::clear();
        Loader::unregister();
    }

}
