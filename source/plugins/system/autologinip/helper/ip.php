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
	 * @return boolean
	 */
	public function matchIp($ip)
	{
		// If the IP is empty always fail
		$ip = trim($ip);
		$ip = strtolower($ip);

		if (empty($ip))
		{
			return false;
		}

		// Current IP
		$currentIp = $this->getCurrentIpAddress();

		// Handle multiple definitions
		$ips = explode(',', $ip);

		foreach ($ips as $ip)
		{
			if ($ip === $currentIp)
			{
				return true;
			}

			try
			{
				$rt = $this->matchIpPattern($ip, $currentIp);
			}
			catch (Exception $e)
			{
				$rt = false;
			}

			if ($rt === true)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Match a single IP string against the current IP
	 *
	 * @param string $ip
	 * @param string $currentIp
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function matchIpPattern($ip, $currentIp)
	{
		// Check for a valid IP
		$ip = trim($ip);

		if (strlen($ip) < 3)
		{
			throw new \Exception('IP does not match minimum length');
		}

		// Handle direct matches
		if ($currentIp == $ip)
		{
			return true;
		}

		if (strstr($currentIp, ':') && strstr($ip, ':'))
		{
			$ipParts = explode(':', $currentIp);

			if (count($ipParts) != 8)
			{
				$currentIp = $this->ipv6octetify($currentIp);
			}
		}

		if (strstr($ip, '-') && $this->isIpRangeMatch($ip, $currentIp))
		{
			return true;
		}

		if (strstr($ip, '*') && $this->isIpWildcardMatch($ip, $currentIp))
		{
			return true;
		}

		return false;
	}

	/**
	 * Fix IPv6 parts
	 *
	 * @param string $currentIp
	 *
	 * @return string
	 */
	public function ipv6octetify($currentIp)
	{
		$ipParts = explode(':', $currentIp);
		$outIP   = '';
		$makeUp  = 8 - count($ipParts);

		for ($i = 0; $i < strlen($currentIp); $i++)
		{
			if (substr($currentIp, $i, 1) == ':' && $i < strlen($currentIp) && substr($currentIp, ($i + 1), 1) == ':')
			{
				for ($b = 0; $b <= $makeUp; $b++)
				{
					$outIP .= ':0';
				}
			}
			else
			{
				$outIP .= substr($currentIp, $i, 1);
			}
		}

		if (substr($outIP, 0, 1) == ':')
		{
			$outIP = '0' . $outIP;
		}

		return $outIP;
	}

	/**
	 * Match whether the IP matches a wildcard range (127.0.0.*)
	 *
	 * @param string $ip
	 * @param string $currentIp
	 *
	 * @return bool
	 */
	public function isIpWildcardMatch($ip, $currentIp)
	{
		$ip      = str_replace(':', '.', $ip);
		$ipParts = explode('.', $ip);

		if (count($ipParts) != 4 && count($ipParts) != 8)
		{
			return false;
		}

		$currentIp        = str_replace(':', '.', $currentIp);
		$currentIpParts   = explode('.', $currentIp);
		$currentIpMatches = 0;

		for ($i = 0; $i < 8; $i++)
		{
			if (!isset($ipParts[$i]))
			{
				break;
			}

			if ($ipParts[$i] == $currentIpParts[$i] || $ipParts[$i] == '*')
			{
				$currentIpMatches++;
			}
		}

		if ($currentIpMatches == 4 || $currentIpMatches == 8)
		{
			return true;
		}

		return false;
	}

	/**
	 * Match whether the IP sits within an IP range (127.0.0.1-127.0.0.9)
	 *
	 * @param string $ip
	 * @param string $currentIp
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function isIpRangeMatch($ip, $currentIp)
	{
		$ipRange = explode('-', $ip);

		if (count($ipRange) != 2)
		{
			throw new \Exception('Range detection only supports 2 arguments');
		}

		$ipRangeStart = trim($ipRange[0]);
		$ipRangeEnd   = trim($ipRange[1]);

		$byteIpRangeStart = inet_pton($ipRangeStart);
		$byteIpRangeEnd = inet_pton($ipRangeEnd);
		$byteCurrentIp = inet_pton($currentIp);

		if ((strlen($byteCurrentIp) == strlen($byteIpRangeStart))
			&&  ($byteCurrentIp >= $byteIpRangeStart && $byteCurrentIp <= $byteIpRangeEnd)) {
			return true;
		}

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
	public function getCurrentIpAddress()
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
			$ip     = array_shift($iplist);
		}

		return $ip;
	}
}
