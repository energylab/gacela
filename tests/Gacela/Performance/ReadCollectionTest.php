<?php

class ReadCollectionTest extends \Test\GUnit\Extensions\Database\TestCase {

	public function getDataSet() 
	{
		$tests = array();

		for($i=0;$i<250;$i++) {
			$tests[] = array('testName' => 'Test'.$i);
		}
		
		return $this->createArrayDataSet(
			array(
				'tests' => $tests
			)
		);
	}

	public function testIterateLessThan30Milliseconds()
	{
		$start = microtime(true);

		$col = \Gacela\Gacela::instance()->findAll('Test');

		foreach($col as $model) {}

		$end = microtime(true);

		$time = $end-$start;

		$this->assertLessThan(0.03, $time, 'Elapsed time: '.round($time, 3));
	}

	/**
	 * For some reason this only passes when run in isolation
	 * If I run the full test suite then it fails
	 */
	public function testAsArrayLessThan100Milliseconds()
	{
		$start = microtime(true);

		$col = \Gacela\Gacela::instance()->findAll('Test');

		$col->asArray('id', 'testName', 'started');

		$end = microtime(true);
		
		$time = $end-$start;

		$this->assertLessThan(0.030, $time, 'Elapsed time: '.round($time, 3));
	}
}
