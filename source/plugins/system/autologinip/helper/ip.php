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
 * AutoLoginIp IP helpers
 */
class AutoLoginIpHelperIp
{

	/**
	 * Helper-method to match a string against the current IP
	 *
	 * @param   string $ip IP address
	 *
	 * @return bool
	 */
	public function matchIp($ip)
	{
		// If the IP is empty always fail
		$ip = trim($ip);

		if (empty($ip))
		{
			return false;
		}

		// Current IP
		$currentIp = $this->getIpAddress();

		// Handle multiple definitions
		$ips = explode(',', $ip);

		foreach ($ips as $ip)
		{
			// Check for a valid IP
			$ip = trim($ip);

			if (strlen($ip) < 3)
			{
				continue;
			}

			// Handle direct matches
			if ($currentIp == $ip)
			{
				return true;

			}
			elseif (strstr($ip, '-'))
			{
				// Handle ranges
				$ipRange = explode('-', $ip);

				if (count($ipRange) != 2)
				{
					continue;
				}

				$ipRangeStart = trim($ipRange[0]);
				$ipRangeEnd = trim($ipRange[1]);

				if (version_compare($currentIp, $ipRangeStart, '>=') && version_compare($currentIp, $ipRangeEnd, '<='))
				{
					return true;
				}

				// Handle wildcards
			}
			elseif (strstr($ip, '*'))
			{
				$ipParts = explode('.', $ip);

				if (count($ipParts) != 4)
				{
					continue;
				}

				$currentIpParts = explode('.', $currentIp);
				$currentIpMatches = 0;

				for ($i = 0; $i < 4; $i++)
				{
					if ($ipParts[$i] == $currentIpParts[$i] || $ipParts[$i] == '*')
					{
						$currentIpMatches++;
					}
				}

				if ($currentIpMatches == 4)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Return the current IP address
	 *
	 * @return  string
	 */
	public function getIpAddress()
	{
		$ip = $_SERVER['REMOTE_ADDR'];

		// Fix the IP-address
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
		{
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED']))
		{
			$ip = $_SERVER['HTTP_X_FORWARDED'];

		}
		elseif (!empty($_SERVER['HTTP_FORWARDED_FOR']))
		{
			$ip = $_SERVER['HTTP_FORWARDED_FOR'];

		}
		elseif (!empty($_SERVER['HTTP_CF_CONNECTING_IP']))
		{
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];

		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$ip = array_shift($iplist);
		}

		return $ip;
	}
}
