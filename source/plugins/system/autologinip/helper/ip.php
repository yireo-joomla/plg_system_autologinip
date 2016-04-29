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
			$this->matchIpPattern($ip, $currentIp);
		}

		return false;
	}

	/**
	 * Match a single IP string against the current IP
	 *
	 * @param $ip
	 * @param $currentIp
	 *
	 * @return bool
	 */
	public function matchIpPattern($ip, $currentIp)
	{
		// Check for a valid IP
		$ip = trim($ip);

		if (strlen($ip) < 3)
		{
			return false;
		}

		// Handle direct matches
		if ($currentIp == $ip)
		{
			return true;
		}

		if (strstr($ip, '-') && $this->isIpRangeMatch($ip, $currentIp))
		{
			return true;
		}

		if (strstr($ip, '*') && $this->isIpRangeMatch($ip, $currentIp))
		{
			return true;
		}

		return false;
	}

	/**
	 * Match whether the IP matches a wildcard range (127.0.0.*)
	 *
	 * @param $ip
	 * @param $currentIp
	 *
	 * @return bool
	 */
	public function isIpWildcardMatch($ip, $currentIp)
	{
		$ipParts = explode('.', $ip);

		if (count($ipParts) != 4)
		{
			return false;
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

		return false;
	}

	/**
	 * Match whether the IP sits within an IP range (127.0.0.1-127.0.0.9)
	 *
	 * @param $ip
	 * @param $currentIp
	 *
	 * @return bool
	 */
	public function isIpRangeMatch($ip, $currentIp)
	{
		$ipRange = explode('-', $ip);

		if (count($ipRange) != 2)
		{
			return false;
		}

		$ipRangeStart = trim($ipRange[0]);
		$ipRangeEnd = trim($ipRange[1]);

		if (version_compare($currentIp, $ipRangeStart, '>=') && version_compare($currentIp, $ipRangeEnd, '<='))
		{
			return true;
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
			return $_SERVER['HTTP_CLIENT_IP'];
		}

		if (!empty($_SERVER['HTTP_X_FORWARDED']))
		{
			return $_SERVER['HTTP_X_FORWARDED'];
		}

		if (!empty($_SERVER['HTTP_FORWARDED_FOR']))
		{
			return $_SERVER['HTTP_FORWARDED_FOR'];
		}

		if (!empty($_SERVER['HTTP_CF_CONNECTING_IP']))
		{
			return $_SERVER['HTTP_CF_CONNECTING_IP'];

		}

		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$ip = array_shift($iplist);
		}

		return $ip;
	}
}
