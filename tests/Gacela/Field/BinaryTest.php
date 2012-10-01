<?php
namespace Gacela\Field;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-10-01 at 15:13:51.
 */
class BinaryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Binary
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = (object) array(
			'type' => 'binary',
			'length' => 255,
			'null' => false
		);
    }

    /**
     * @covers Gacela\Field\Binary::validate
     * @todo   Implement testValidate().
     */
    public function testValidateLengthCode()
    {

    }

	public function testValidateNullCode()
	{
		$this->assertEquals(Binary::NULL_CODE, Binary::validate($this->object, null));
	}

	public function testValidatePassNull()
	{
		$this->object->null = true;

		$this->assertTrue(Binary::validate($this->object, null));
	}

    /**
     * @covers Gacela\Field\Binary::transform
     * @todo   Implement testTransform().
     */
    public function testTransform()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}
