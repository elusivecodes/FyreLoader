<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Loader\Loader;
use Fyre\Utility\Path;
use PHPUnit\Framework\TestCase;

final class LoaderTest extends TestCase
{

    public function testClassMap(): void
    {
        Loader::addClassMap([
            'TestClass' => 'tests/classes/TestClass.php'
        ]);

        $this->assertTrue(
            \TestClass::test()
        );
    }

    public function testNamespace(): void
    {
        Loader::addNamespaces([
            'Demo' => 'tests/classes/Demo'
        ]);

        $this->assertTrue(
            \Demo\TestClass::test()
        );
    }

    public function testNamespaceTrailingSlash(): void
    {
        Loader::addNamespaces([
            'Demo' => 'tests/classes/Demo/'
        ]);

        $this->assertTrue(
            \Demo\TestClass::test()
        );
    }

    public function testNamespaceArray(): void
    {
        Loader::addNamespaces([
            'Demo' => [
                'tests/classes/Demo'
            ]
        ]);

        $this->assertTrue(
            \Demo\TestClass::test()
        );
    }

    public function testNamespaceDeep(): void
    {
        Loader::addNamespaces([
            'Demo' => 'tests/classes/Demo'
        ]);

        $this->assertTrue(
            \Demo\Deep\TestClass::test()
        );
    }

    public function testLoadComposer(): void
    {
        Loader::loadComposer('tests/Mock/autoload.php');

        $this->assertSame(
            [
                Path::resolve('src')
            ],
            Loader::getNamespace('Fyre')
        );
    }

    public function testGetClassMap(): void
    {
        Loader::addClassMap([
            'Test\Example' => 'other/classes/Example.php',
            'Test\Deep\Another' => 'files/Deep/Another.php'
        ]);

        $this->assertSame(
            [
                'Test\Example' => Path::resolve('other/classes/Example.php'),
                'Test\Deep\Another' => Path::resolve('files/Deep/Another.php')
            ],
            Loader::getClassMap()
        );
    }

    public function testGetNamespace(): void
    {
        Loader::addNamespaces([
            'Test' => 'tests/',
            'Demo' => 'tests/classes/Demo'
        ]);

        $this->assertSame(
            [
                Path::resolve('tests/classes/Demo')
            ],
            Loader::getNamespace('Demo')
        );
    }

    public function testGetNamespaceInvalid(): void
    {
        $this->assertSame(
            [],
            Loader::getNamespace('Demo')
        );
    }

    public function testGetNamespacePaths(): void
    {
        Loader::addClassMap([
            'Test\Example' => 'other/classes/Example.php',
            'Test\Deep\Another' => 'files/Deep/Another.php'
        ]);

        Loader::addNamespaces([
            'Test' => 'tests/'
        ]);

        $this->assertSame(
            [
                Path::resolve('tests'),
                Path::resolve('other/classes'),
                Path::resolve('files')
            ],
            Loader::getNamespacePaths('Test')
        );
    }

    public function testGetNamespaces(): void
    {
        Loader::addNamespaces([
            'Test' => 'tests/',
            'Demo' => 'tests/classes/Demo'
        ]);

        $this->assertSame(
            [
                'Test\\' => [
                    Path::resolve('tests')
                ],
                'Demo\\' => [
                    Path::resolve('tests/classes/Demo')
                ]
            ],
            Loader::getNamespaces()
        );
    }

    public function testHasNamespace(): void
    {
        Loader::addNamespaces([
            'Demo' => 'tests/classes/Demo'
        ]);

        $this->assertTrue(
            Loader::hasNamespace('Demo')
        );
    }

    public function testHasNamespaceInvalid(): void
    {
        $this->assertFalse(
            Loader::hasNamespace('Demo')
        );
    }

    public function testRemoveClass(): void
    {
        Loader::addClassMap([
            'Test\Example' => 'other/classes/Example.php',
            'Test\Deep\Another' => 'files/Deep/Another.php'
        ]);

        $this->assertTrue(
            Loader::removeClass('Test\Example')
        );

        $this->assertSame(
            [
                'Test\Deep\Another' => Path::resolve('files/Deep/Another.php')
            ],
            Loader::getClassMap()
        );
    }

    public function testRemoveClassInvalid(): void
    {
        $this->assertFalse(
            Loader::removeClass('Test')
        );
    }

    public function testRemoveNamespace(): void
    {
        Loader::addNamespaces([
            'Demo' => 'tests/classes/Demo'
        ]);

        $this->assertTrue(
            Loader::removeNamespace('Demo')
        );

        $this->assertFalse(
            Loader::hasNamespace('Demo')
        );
    }

    public function testRemoveNamespaceInvalid(): void
    {
        $this->assertFalse(
            Loader::removeNamespace('Demo')
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
