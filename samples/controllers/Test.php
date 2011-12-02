<?php

class Test extends Controller
{
	public function index()
	{
		exit(debug(\Gacela::instance()->loadMapper('house')->find(1)->students->search(array('wizardId' => 1))));
	}
}