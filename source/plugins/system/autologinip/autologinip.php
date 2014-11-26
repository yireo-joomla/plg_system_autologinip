<?php
/**
 * Joomla! System plugin - Auto Login IP
 *
 * @author     Yireo <info@yireo.com>
 * @copyright  Copyright 2014 Yireo.com. All rights reserved
 * @license    GNU Public License
 * @link       http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Import parent library
jimport('joomla.plugin.plugin');

/**
 * IP Authentication System Plugin
 */
class plgSystemAutoLoginIp extends JPlugin
{
	/**
	 * Catch the event onAfterInitialise
	 *
	 * @return null
	 */
	public function onAfterRoute()
	{
		// Load system variables
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$user = JFactory::getUser();

		// Only allow usage from within the frontend
		if ($app->getName() != 'site')
		{
			return;
		}

		// If the current user is not a guest, authentication has already occurred
		if ($user->guest == 0)
		{
			return;
		}

		// Skip AJAX requests
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		{
			return;
		}

		// Skip non-page requests
		$format = $jinput->getCmd('format');
		$tmpl = $jinput->getCmd('tmpl');
		$type = $jinput->getCmd('type');

		if (in_array($format, array('raw', 'feed')) || in_array($type, array('rss', 'atom')) || $tmpl == 'component')
		{
			return;
		}

		// Initialize the IP-match
		$ipMatch = false;

		// Initialize the user-ID
		$userid = trim($this->params->get('userid'));

		// Check for an IP-match for the main IP-parameter
		$ip = $this->params->get('ip');

		if (!empty($ip))
		{
			$ipMatch = $this->matchIp($ip);
		}

		// Try to use the user/IP-mapping instead
		$mappings = $this->params->get('userid_ip');

		if ($ipMatch != true && !empty($mappings))
		{
			$mappings = explode("\n", $mappings);

			foreach ($mappings as $mapping)
			{
				$mapping = explode('=', $mapping);

				if ($this->matchIp($mapping[1]))
				{
					$ipMatch = true;
					$userid = (int) trim($mapping[0]);
					break;
				}
			}
		}

		// If no IP-match was found, don't do anything else
		if ($ipMatch == false)
		{
			return;
		}

		// Check for an userid
		if (empty($userid) && $userid < 1)
		{
			return;
		}

		// Load the user
		$user = JFactory::getUser();
		$user->load($userid);

		if (!$user->id > 0)
		{
			return;
		}

		// Allow a page to redirect the user to
		$redirect = $this->params->get('redirect');

		if ($redirect > 0)
		{
			$redirect = JRoute::_('index.php?Itemid=' . $redirect);
		}
		else
		{
			$redirect = null;
		}

		// Construct the options
		$options = array();
		$options['remember'] = true;
		$options['return'] = $redirect;
		$options['action'] = 'core.login.site';

		// Construct a response
		jimport('joomla.user.authentication');
		JPluginHelper::importPlugin('authentication');
		JPluginHelper::importPlugin('user');
		$authenticate = JAuthentication::getInstance();

		// Construct the response-object
		$response = new JAuthenticationResponse;
		$response->type = 'Joomla';
		$response->email = $user->email;
		$response->fullname = $user->name;
		$response->username = $user->username;
		$response->password = $user->username;
		$response->language = $user->getParam('language');
		$response->status = JAuthentication::STATUS_SUCCESS;
		$response->error_message = null;

		// Authorise this response
		$authorisations = $authenticate->authorise($response, $options);

		// Run the login-event
		$results = $app->triggerEvent('onUserLogin', array((array) $response, $options));

		// Redirect if needed
		if (!empty($redirect))
		{
			$app->redirect($redirect);

			return;
		}
	}

	/**
	 * Helper-method to match a string against the current IP
	 *
	 * @param   string  $ip  IP address
	 *
	 * @return bool
	 */
	protected function matchIp($ip)
	{
		// If the IP is empty always fail
		$ip = trim($ip);

		if (empty($ip))
		{
			return false;
		}

		// Current IP
		$currentIp = $_SERVER['REMOTE_ADDR'];

		// Handle multiple definitions
		$ips = explode(',', $ip);

		foreach ($ips as $ip)
		{
			// Check for a valid IP
			$ip = trim($ip);

			if (strlen($ip) < 8)
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
}
