<?php

class Test extends Controller
{
	public function index()
	{
		exit(\Gacela::instance()->loadMapper('house')->findWithAliases());
	}
}