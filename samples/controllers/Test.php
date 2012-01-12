<?php

class Test extends Controller
{
	public function index()
	{
		exit(debug(\Gacela::instance()->loadMapper('student')->findByHouse()));
	}
}