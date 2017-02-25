<?php
/**
 * Test class for Joomla plugin "Auto Login IP"
 *
 * @author     Jisse Reitsma <jisse@yireo.com>
 * @copyright  Copyright 2016 Jisse Reitsma
 * @license    GNU Public License version 3 or later
 * @link       https://www.yireo.com/
 */

use Yireo\Test\PluginCase;

/**
 * Class PlgSystemAutoLoginIpTest
 */
class PlgSystemAutoLoginIpTest extends PluginCase
{
	/**
	 * @var string
	 */
	protected $pluginName = 'autologinip';

	/**
	 * @var string
	 */
	protected $pluginGroup = 'system';

	/**
	 * @var array
	 */
	protected $pluginParams = [
		'redirect' => 1,
	];

	/**
	 * @return void
	 */
	public function testGetRedirectUrl()
	{
		$plugin = $this->getPluginInstance();
		$method = $this->getObjectMethod($plugin, 'getRedirectUrl');
		$this->assertNotEmpty($method->invokeArgs($plugin, []));
	}
}

