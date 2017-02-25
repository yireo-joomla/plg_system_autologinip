<?php
/**
 * PHPUnit parent class for Joomla plugins
 *
 * @author     Jisse Reitsma <jisse@yireo.com>
 * @copyright  Copyright 2016 Jisse Reitsma
 * @license    GNU Public License version 3 or later
 * @link       https://www.yireo.com/
 */

use Yireo\Test\JoomlaCase;

/**
 * Class PluginTest
 */
class AutoLoginIpHelperIpTest extends JoomlaCase
{
	/**
	 * @var AutoLoginIpHelperIp
	 */
	protected $target;

	/**
	 * Setup the parent
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		require_once DOCUMENT_ROOT . '/plugins/system/autologinip/helper/ip.php';
		$targetClassName = $this->getTargetClassName();
		$this->target    = new $targetClassName;
	}

	/**
	 * @test AutoLoginIpHelperIp::matchIpPattern
	 * @return void
	 */
	public function testMatchIp()
	{
		$currentIp = '192.168.1.1';

		$this->assertTrue($this->target->matchIpPattern($currentIp, $currentIp));
		$this->assertFalse($this->target->matchIpPattern('192.168.1.2', $currentIp));
	}

	/**
	 * @test AutoLoginIpHelperIp::matchIpPattern
	 * @return void
	 */
	public function testMatchIpWildcard()
	{
		$currentIp = '192.168.1.1';

		$this->assertTrue($this->target->matchIpPattern('192.168.1.*', $currentIp));
		$this->assertTrue($this->target->matchIpPattern('192.168.*.*', $currentIp));
		$this->assertFalse($this->target->matchIpPattern('192.168.2.*', $currentIp));
	}

	/**
	 * @test AutoLoginIpHelperIp::matchIpPattern
	 * @return void
	 */
	public function testMatchIpRang()
	{
		$currentIp = '192.168.1.1';

		$this->assertTrue($this->target->matchIpPattern('192.168.1.1-192.168.1.10', '192.168.1.2'));
		$this->assertFalse($this->target->matchIpPattern('192.168.2.1-192.168.2.10', $currentIp));
	}

	/**
	 * @test AutoLoginIpHelperIp::matchIpPattern
	 * @return void
	 */
	public function testMatchIp6()
	{
		$currentIp = 'fe80:0:0:0:200:f8ff:fe21:67ce';

		$this->assertTrue($this->target->matchIpPattern($currentIp, $currentIp));
		$this->assertFalse($this->target->matchIpPattern('fe80::::200:f8ff:fe21:67ce', $currentIp));
		$this->assertFalse($this->target->matchIpPattern('fe80:0:0:0:200:f8ff:fe21:67ca', $currentIp));
	}

	/**
	 * @test AutoLoginIpHelperIp::matchIpPattern
	 * @return void
	 */
	public function testMatchIp6Wildcard()
	{
		$currentIp = 'fe80:0:0:0:200:f8ff:fe21:67ce';

		$this->assertTrue($this->target->matchIpPattern('fe80:0:0:0:200:f8ff:fe21:*', $currentIp));
		$this->assertTrue($this->target->matchIpPattern('fe80:0:0:0:200:f8ff:*:*', $currentIp));
		$this->assertFalse($this->target->matchIpPattern('fe80:0:0:0:200:f8ff:fe22:*', $currentIp));
	}

	/**
	 * @test AutoLoginIpHelperIp::matchIpPattern
	 * @return void
	 */
	public function testMatchIp6Range()
	{
		$currentIp = 'fe80:0:0:0:200:f8ff:fe21:67ce';

		$this->assertTrue($this->target->matchIpPattern('fe80:0:0:0:200:f8ff:fe21:67c0-fe80:0:0:0:200:f8ff:fe21:67cf', $currentIp));
		$this->assertFalse($this->target->matchIpPattern('fe80:0:0:0:200:f8ff:fe21:67c0-fe80:0:0:0:200:f8ff:fe21:67cd', $currentIp));
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

