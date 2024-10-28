<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Loader\Loader;
use Fyre\Utility\Path;
use PHPUnit\Framework\TestCase;

final class LoaderTest extends TestCase
{
    protected Loader $loader;

    public function testClassMap(): void
    {
        $this->assertSame(
            $this->loader,
            $this->loader->addClassMap([
                'TestClass' => 'tests/classes/TestClass.php',
            ])
        );

        $this->assertTrue(
            \TestClass::test()
        );
    }

    public function testGetClassMap(): void
    {
        $this->loader->addClassMap([
            'Test\Example' => 'other/classes/Example.php',
            'Test\Deep\Another' => 'files/Deep/Another.php',
        ]);

        $this->assertSame(
            [
                'Test\Example' => Path::resolve('other/classes/Example.php'),
                'Test\Deep\Another' => Path::resolve('files/Deep/Another.php'),
            ],
            $this->loader->getClassMap()
        );
    }

    public function testGetNamespace(): void
    {
        $this->assertSame(
            $this->loader,
            $this->loader->addNamespaces([
                'Test' => 'tests/',
                'Demo' => 'tests/classes/Demo',
            ])
        );

        $this->assertSame(
            [
                Path::resolve('tests/classes/Demo'),
            ],
            $this->loader->getNamespace('Demo')
        );
    }

    public function testGetNamespaceInvalid(): void
    {
        $this->assertSame(
            [],
            $this->loader->getNamespace('Demo')
        );
    }

    public function testGetNamespacePaths(): void
    {
        $this->loader->addClassMap([
            'Test\Example' => 'other/classes/Example.php',
            'Test\Deep\Another' => 'files/Deep/Another.php',
        ]);

        $this->loader->addNamespaces([
            'Test' => 'tests/',
        ]);

        $this->assertSame(
            [
                Path::resolve('tests'),
                Path::resolve('other/classes'),
                Path::resolve('files'),
            ],
            $this->loader->getNamespacePaths('Test')
        );
    }

    public function testGetNamespaces(): void
    {
        $this->loader->addNamespaces([
            'Test' => 'tests/',
            'Demo' => 'tests/classes/Demo',
        ]);

        $this->assertSame(
            [
                'Test\\' => [
                    Path::resolve('tests'),
                ],
                'Demo\\' => [
                    Path::resolve('tests/classes/Demo'),
                ],
            ],
            $this->loader->getNamespaces()
        );
    }

    public function testHasNamespace(): void
    {
        $this->loader->addNamespaces([
            'Demo' => 'tests/classes/Demo',
        ]);

        $this->assertTrue(
            $this->loader->hasNamespace('Demo')
        );
    }

    public function testHasNamespaceInvalid(): void
    {
        $this->assertFalse(
            $this->loader->hasNamespace('Demo')
        );
    }

    public function testLoadComposer(): void
    {
        $this->loader->loadComposer('tests/Mock/autoload.php');

        $this->assertSame(
            [
                Path::resolve('src'),
            ],
            $this->loader->getNamespace('Fyre')
        );
    }

    public function testNamespace(): void
    {
        $this->loader->addNamespaces([
            'Demo' => 'tests/classes/Demo',
        ]);

        $this->assertTrue(
            \Demo\TestClass::test()
        );
    }

    public function testNamespaceArray(): void
    {
        $this->loader->addNamespaces([
            'Demo' => [
                'tests/classes/Demo',
            ],
        ]);

        $this->assertTrue(
            \Demo\TestClass::test()
        );
    }

    public function testNamespaceDeep(): void
    {
        $this->loader->addNamespaces([
            'Demo' => 'tests/classes/Demo',
        ]);

        $this->assertTrue(
            \Demo\Deep\TestClass::test()
        );
    }

    public function testNamespaceTrailingSlash(): void
    {
        $this->loader->addNamespaces([
            'Demo' => 'tests/classes/Demo/',
        ]);

        $this->assertTrue(
            \Demo\TestClass::test()
        );
    }

    public function testRemoveClass(): void
    {
        $this->loader->addClassMap([
            'Test\Example' => 'other/classes/Example.php',
            'Test\Deep\Another' => 'files/Deep/Another.php',
        ]);

        $this->assertSame(
            $this->loader,
            $this->loader->removeClass('Test\Example')
        );

        $this->assertSame(
            [
                'Test\Deep\Another' => Path::resolve('files/Deep/Another.php'),
            ],
            $this->loader->getClassMap()
        );
    }

    public function testRemoveClassInvalid(): void
    {
        $this->assertSame(
            $this->loader,
            $this->loader->removeClass('Test')
        );
    }

    public function testRemoveNamespace(): void
    {
        $this->loader->addNamespaces([
            'Demo' => 'tests/classes/Demo',
        ]);

        $this->assertSame(
            $this->loader,
            $this->loader->removeNamespace('Demo')
        );

        $this->assertFalse(
            $this->loader->hasNamespace('Demo')
        );
    }

    public function testRemoveNamespaceInvalid(): void
    {
        $this->assertSame(
            $this->loader,
            $this->loader->removeNamespace('Demo')
        );
    }

    protected function setUp(): void
    {
        $this->loader = new Loader();

        $this->assertSame(
            $this->loader,
            $this->loader->register()
        );
    }

    protected function tearDown(): void
    {
        $this->assertSame(
            $this->loader,
            $this->loader->unregister()
        );
    }
}
