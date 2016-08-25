<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\Namespaze
 */
class NamespazeTest extends \PHPUnit_Framework_TestCase
{
    public function testAcceptsEmptyNamespace()
    {
        $this->assertEquals('', new Namespaze([]));
    }

    public function testAcceptsValidNamespaceParts()
    {
        $this->assertEquals('a\b\c', new Namespaze(['a', 'b', 'c']));
    }

    public function testDetectsInvalidNamespaceParts()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Namespaze([1]);
    }

    public function testDepthOfEmptyNamespaceIsZero()
    {
        $this->assertEquals(0, (new Namespaze([]))->depth());
    }

    public function testDepthOfNamespace()
    {
        $this->assertEquals(2, (new Namespaze(['A', 'B']))->depth());
    }

    public function testReduceToMaxDepth()
    {
        $this->assertEquals(new Namespaze(['A', 'B']), (new Namespaze(['A', 'B', 'C', 'D']))->reduceToDepth(2));
    }

    public function testDoNotReduceForMaxDepthZero()
    {
        $this->assertEquals(new Namespaze(['A', 'B']), (new Namespaze(['A', 'B']))->reduceToDepth(0));
    }

    public function testEquals()
    {
        $this->assertTrue((new Namespaze(['A', 'B']))->equals(new Namespaze(['A', 'B'])));
        $this->assertTrue((new Namespaze([]))->equals(new Namespaze([])));
        $this->assertFalse((new Namespaze(['A', 'B']))->equals(new Namespaze(['A'])));
        $this->assertFalse((new Namespaze(['A', 'B']))->equals(new Namespaze([])));
    }
}