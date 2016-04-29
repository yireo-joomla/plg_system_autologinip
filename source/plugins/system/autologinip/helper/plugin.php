<?php
/**
 * Joomla! System plugin - Auto Login IP
 *
 * @author     Yireo <info@yireo.com>
 * @copyright  Copyright 2015 Yireo.com. All rights reserved
 * @license    GNU Public License
 * @link       http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * AutoLoginIp plugin helper
 */
class AutoLoginIpHelperPlugin
{
	/**
	 * @var Joomla\Registry\Registry
	 */
	protected $params;

	/**
	 * @param $params
	 */
	public function __construct($params)
	{
		$this->params = $params;
	}

	/**
	 * Helper method to return the mapping of user ID and IP
	 *
	 * @return array
	 */
	public function getMapping()
	{
		// Try to use the user/IP-mapping instead
		$mappings = $this->params->get('userid_ip');
		$array = array();

		if (!empty($mappings))
		{
			$mappings = explode("\n", $mappings);

			foreach ($mappings as $mapping)
			{
				$mapping = explode('=', $mapping);
				$userid = (int) trim($mapping[0]);
				$ip = trim($mapping[1]);

				if (!empty($ip) && !empty($userid))
				{
					$array[] = ['ip' => $ip, 'user' => $userid];
				}
			}
		}

		return $array;
	}
}