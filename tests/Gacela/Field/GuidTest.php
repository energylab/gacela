<?php
namespace Gacela\Field;

class GuidTest extends \PHPUnit_Framework_TestCase
{

	protected $meta;

	/**
	 * @var String
	 */
	protected $object;

	protected function setUp()
	{
		$this->object = new Guid;

		$this->meta = (object) array(
			'type' => 'guid',
			'length' => 10,
			'null' => false,
			'default' => false
		);
	}

	public function providerPass()
	{
		return array
		(
			array(null),
			array(false),
			array(''),
			array('ABC123EFG0')
		);
	}

    /**
     * @covers Gacela\Field\Guid::validate
     * @dataProvider providerPass
     */
    public function testValidatePass($val)
    {
		$this->assertTrue($this->object->validate($this->meta, $val), "Failed to validate: ".print_r($val, true));
    }

	public function testValidateLengthCode()
	{
		$this->assertEquals(String::LENGTH_CODE, $this->object->validate($this->meta, 'This is a string longer than 10 characters.'));
	}

    /**
     * @covers Gacela\Field\String::transform
     * @todo   Implement testTransform().
     */
    public function testTransformIn()
    {
		$string = 'I am a very fine string';

        $this->assertSame($string, $this->object->transform($this->meta, $string, true));
    }

	public function testTransformOut()
	{
		$string = 'New String';

		$this->assertSame($string, $this->object->transform($this->meta, $string, false));
	}
}
