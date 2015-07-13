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

// Import parent library
jimport('joomla.plugin.plugin');

/**
 * IP Authentication System Plugin
 */
class PlgSystemAutoLoginIp extends JPlugin
{
	/**
	 * @var JApplication
	 */
	protected $app;

	/**
	 * @var JInput
	 */
	protected $jinput;

	/**
	 * @var AutoLoginIpHelperIp
	 */
	protected $ipHelper;

	/**
	 * @var AutoLoginIpHelperPlugin
	 */
	protected $pluginHelper;

	/**
	 * Method to initialize a bunch of stuff for this plugin
	 */
	public function init()
	{
		$this->app = JFactory::getApplication();
		$this->jinput = $this->app->input;

		require_once __DIR__ . '/helper/ip.php';
		$this->ipHelper = new AutoLoginIpHelperIp;

		require_once __DIR__ . '/helper/plugin.php';
		$this->pluginHelper = new AutoLoginIpHelperPlugin;
	}

	/**
	 * Catch the event onAfterInitialise
	 *
	 * @return null
	 */
	public function onAfterRoute()
	{
		$this->init();

		// Check if this plugin is allowed to run
		if ($this->allowLogin() == false)
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
			$ipMatch = $this->ipHelper->matchIp($ip);
		}

		// Try to use the user/IP-mapping instead
		$mappings = $this->getMapping();

		if ($ipMatch != true && !empty($mappings))
		{
			foreach ($mappings as $mappingUserid => $mappingIp)
			{
				if ($this->ipHelper->matchIp($mappingIp))
				{
					$ipMatch = true;
					$userid = $mappingUserid;
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

		if (!$user->id > 0 || !$user instanceof JUser)
		{
			return;
		}

		// Login the user
		$this->doLogin($user);
	}

	/**
	 * Helper method to return the mapping of user ID and IP
	 *
	 * @return array
	 */
	protected function getMapping()
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
					$array[$userid] = $ip;
				}
			}
		}

		return $array;
	}

	/**
	 * Helper-method to get the redirect URL for this login procedure
	 *
	 * @return string
	 */
	protected function getRedirectUrl()
	{
		$redirect = $this->params->get('redirect');

		if ($redirect > 0)
		{
			$redirect = JRoute::_('index.php?Itemid=' . $redirect);
		}
		else
		{
			$redirect = null;
		}

		return $redirect;
	}

	/**
	 * Helper-method to login a specific user
	 *
	 * @param JUser $user
	 *
	 * @return bool
	 */
	protected function doLogin(JUser $user)
	{
		// Allow a page to redirect the user to
		$redirect = $this->getRedirectUrl();

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
		$authenticate->authorise($response, $options);

		// Run the login-event
		$this->app->triggerEvent('onUserLogin', array((array) $response, $options));

		// Set a cookie so that we don't do this twice
		$cookie = $this->app->input->cookie;
		$cookie->set('autologinip', 1, 0);

		// Redirect if needed
		if (!empty($redirect))
		{
			$this->app->redirect($redirect);
		}
	}

	/**
	 * Helper-method to determine whether a login is allowed or not
	 *
	 * @return bool
	 */
	protected function allowLogin()
	{
		// Load system variables

		$user = JFactory::getUser();

		// Only allow usage from within the right app
		$allowedApp = $this->params->get('application', 'site');

		if ($this->app->getName() != $allowedApp && !in_array($allowedApp, array('both', 'all')))
		{
			return false;
		}

		// Skip AJAX requests
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		{
			return false;
		}

		// Skip non-page requests
		$format = $this->jinput->getCmd('format');
		$tmpl = $this->jinput->getCmd('tmpl');
		$type = $this->jinput->getCmd('type');

		if (in_array($format, array('raw', 'feed')) || in_array($type, array('rss', 'atom')) || $tmpl == 'component')
		{
			return false;
		}

		// If the current user is not a guest, authentication has already occurred
		if ($user->guest == 0)
		{
			return false;
		}

		// Check for the cookie
		if ($this->app->input->cookie->get('autologinip') == 1)
		{
			return false;
		}

		return true;
	}
}
