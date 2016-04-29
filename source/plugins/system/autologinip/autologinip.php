<?php
/**
 * Joomla! System plugin - Auto Login IP
 *
 * @author     Yireo <info@yireo.com>
 * @copyright  Copyright 2016 Yireo.com. All rights reserved
 * @license    GNU Public License
 * @link       https://www.yireo.com
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
	 * @var JApplicationCms
	 */
	protected $app;

	/**
	 * @var JInput
	 */
	protected $input;

	/**
	 * @var AutoLoginIpHelperIp
	 */
	protected $ipHelper;

	/**
	 * @var boolean
	 */
	protected $ipMatch = false;

	/**
	 * @var int
	 */
	protected $userId = 0;

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
		$this->input = $this->app->input;

		require_once __DIR__ . '/helper/ip.php';
		$this->ipHelper = new AutoLoginIpHelperIp;

		require_once __DIR__ . '/helper/plugin.php';
		$this->pluginHelper = new AutoLoginIpHelperPlugin($this->params);
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

		$this->matchIp();

		// If no IP-match was found, don't do anything else
		if ($this->ipMatch == false)
		{
			return;
		}

		// Load the user
		$user = $this->loadUser();

		if ($user == false)
		{
			return;
		}

		// Login the user
		$this->doLogin($user);
	}

	/**
	 * @return bool|JUser
	 */
	protected function loadUser()
	{
		// Check for an userid
		if (empty($this->userId) && $this->userId < 1)
		{
			return false;
		}

		// Load the user
		$user = JFactory::getUser();
		$user->load($this->userId);

		if (!$user->id > 0 || !$user instanceof JUser)
		{
			return false;
		}

		return $user;
	}

	/**
	 * @return bool
	 */
	protected function matchIp()
	{
		// Initialize the user-ID
		$this->userId = trim($this->params->get('userid'));

		// Check for an IP-match for the main IP-parameter
		$ip = $this->params->get('ip');

		if (!empty($ip))
		{
			$this->ipMatch = $this->ipHelper->matchIp($ip);

			return true;
		}

		// Try to use the user/IP-mapping instead
		$mappings = $this->pluginHelper->getMapping();

		if (!empty($mappings))
		{
			foreach ($mappings as $mapping)
			{
				$mappingUserid = $mapping['user'];
				$mappingIp = $mapping['ip'];

				if ($this->ipHelper->matchIp($mappingIp))
				{
					$this->ipMatch = true;
					$this->userId = $mappingUserid;

					return true;
				}
			}
		}

		return false;
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
		if ($this->params->get('cookie') == 1)
		{
			$cookie = $this->app->input->cookie;
			$cookie->set('autologinip', 1, 0);
		}

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

		if ($allowedApp == 'admin')
		{
			$allowedApp = 'administrator';
		}

		if ($this->app->getName() != $allowedApp && !in_array($allowedApp, array('both', 'all')))
		{
			return false;
		}

		// Skip AJAX requests
		if ($this->isAjaxRequest())
		{
			return false;
		}

		// If the current user is not a guest, authentication has already occurred
		if ($user->guest == 0)
		{
			return false;
		}

		// Check for the cookie
		if ($this->params->get('cookie') == 1 && $this->app->input->cookie->get('autologinip') == 1)
		{
			return false;
		}

		return true;
	}

	/**
	 * Check whether the current request is an AJAX or AHAH request
	 *
	 * @return bool
	 */
	protected function isAjaxRequest()
	{
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		{
			return true;
		}

		$format = $this->input->getCmd('format');
		$tmpl = $this->input->getCmd('tmpl');
		$type = $this->input->getCmd('type');

		if (in_array($format, array('raw', 'feed')) || in_array($type, array('rss', 'atom')) || $tmpl == 'component')
		{
			return true;
		}

		return false;
	}
}
