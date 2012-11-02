<?php

namespace Gacela;

class Exception extends \Exception
{
	public static function handler(\Exception $e)
	{
		echo '<pre>'.$e->getMessage().'</pre>';
	}
}
