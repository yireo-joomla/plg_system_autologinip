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
        print_r($params);exit;
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

		if (empty($mappings))
        {
            return $array;
        }

		$mappings = explode("\n", $mappings);

		foreach ($mappings as $mapping)
		{
            if (empty($mapping))
            {
                continue;
            }

			$mapping = explode('=', $mapping);

            if (count($mapping) != 2)
            {
                continue;
            }

			$userid = (int) trim($mapping[0]);
			$ip = trim($mapping[1]);

			if (!empty($ip) && !empty($userid))
			{
				$array[] = ['ip' => $ip, 'user' => $userid];
			}
		}

		return $array;
	}

	/**
	 * @return int
	 */
    protected function getUserIdFromParams()
    {
		return trim($this->params->get('userid'));
    }

	/**
	 * @return string
	 */
    protected function getIpFromParams()
    {
		return $this->params->get('ip');
    }
}
