<?php
/**
 * PHPUnit parent class for Joomla plugins
 *
 * @author     Jisse Reitsma <jisse@yireo.com>
 * @copyright  Copyright 2016 Jisse Reitsma
 * @license    GNU Public License version 3 or later
 * @link       https://www.yireo.com/
 */

/**
 * Class PluginTest
 */
class AutoLoginIpHelperIpTest extends Yireo_Plugin_TestCase
{
	protected $target;

	protected function setUp()
	{
		parent::setUp();

		require_once DOCUMENT_ROOT . '/plugins/system/autologinip/helper/ip.php';
		$targetClassName = $this->getTargetClassName();
		$this->target = new $targetClassName;
	}

	/**
	 * @test AutoLoginIpHelperIp::matchIpPattern
	 */
	public function testMatchIpPattern()
	{
		$this->assertTrue($this->target->matchIpPattern('192.168.1.1', '192.168.1.1'));
		$this->assertFalse($this->target->matchIpPattern('192.168.1.2', '192.168.1.1'));

		$this->assertTrue($this->target->matchIpPattern('192.168.1.*', '192.168.1.1'));
		$this->assertFalse($this->target->matchIpPattern('192.168.2.*', '192.168.1.1'));

		$this->assertTrue($this->target->matchIpPattern('192.168.1.1-192.168.1.10', '192.168.1.2'));
		$this->assertFalse($this->target->matchIpPattern('192.168.1.1-192.168.1.10', '192.168.1.11'));
	}

	/**
	 * @test AutoLoginIpHelperIp::isIpWildcardMatch
	 */
	public function testIsIpWildcardMatch()
	{
		$this->assertTrue($this->target->isIpWildcardMatch('192.168.1.*', '192.168.1.1'));
		$this->assertFalse($this->target->isIpWildcardMatch('192.168.2.*', '192.168.1.1'));
	}
}

