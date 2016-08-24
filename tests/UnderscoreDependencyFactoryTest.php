<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\UnderscoreDependencyFactory
 */
class UnderscoreDependencyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var DependencyFactory */
    private $clazzFactory;

    public function setUp()
    {
        $this->clazzFactory = new UnderscoreDependencyFactory();
    }

    public function testNoUnderscoresNoNamespaces()
    {
        $this->assertEquals(new Clazz('Test'), $this->clazzFactory->createClazzFromStringArray(['Test']));
    }

    public function testPhp5NamespacesStillDetected()
    {
        $this->assertEquals(
            new Clazz('Test', new Namespaze(['A'])),
            $this->clazzFactory->createClazzFromStringArray(['A', 'Test'])
        );
    }

    public function testDetectsNamespacesFromClassWithOnlyUnderscores()
    {
        $this->assertEquals(
            new Clazz('Test', new Namespaze(['A', 'b', 'c'])),
            $this->clazzFactory->createClazzFromStringArray(['A_b_c_Test'])
        );
    }

    public function testMixedNamespacesDetected()
    {
        $this->assertEquals(
            new Clazz('Test', new Namespaze(['A', 'B'])),
            $this->clazzFactory->createClazzFromStringArray(['A', 'B_Test'])
        );
    }
}
