<?php
/**
 * Matomo - free/libre analytics platform
 * Plugin developed for Web Analytics Italia (https://webanalytics.italia.it)
 *
 * Some code is reused from Matomo Piwik\Plugins\CoreHome\LoginWhitelist.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LoginFilterIp;

use Exception;
use Piwik\Config;
use Piwik\IP;
use Piwik\Network\IP as NetworkIp;
use Piwik\Piwik;
use Piwik\Url;

class LoginFilterIp extends \Piwik\Plugin
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $pluginConfig;

    /**
     * Construct a new LoginFilterIp instance.
     */
    public function __construct() {
        parent::__construct();

        $this->pluginConfig = Config::getInstance()->{$this->pluginName};
    }

    /**
     * Get event handlers.
     *
     * @return array the event handlers
     */
    public function registerEvents()
    {
        return [
            'Controller.Login.' => 'loginFilterIP',
            'Controller.Login.index' => 'loginFilterIP',
            'Controller.Login.confirmResetPassword' => 'loginFilterIP',
            'Controller.Login.confirmPassword' => 'loginFilterIP',
            'Controller.Login.resetPassword' => 'loginFilterIP',
            'Controller.Login.login' => 'loginFilterIP',
        ];
    }

    /**
     * Filter IP using the value of the client IP address.
     */
    public function loginFilterIP()
    {
        $this->checkIsAllowedLogin(IP::getIpFromHeader());
    }

    /**
     * Send a response based on the passed client IP address.
     *
     * @param string $ip the address to check
     * 
     * @throws Exception if the passed IP address is not allowed and redirect is not configured
     */
    protected function checkIsAllowedLogin(string $ip)
    {
        if (!$this->isAllowedLogin($ip)) {
            if ($this->shouldRedirectUnallowedIPs()) {
                Url::redirectToUrl($this->pluginConfig['redirect_unallowed_to']);

                return;
            }
            throw new Exception(Piwik::translate('CoreHome_ExceptionNotWhitelistedIP', $ip));
        }
    }

    /**
     * Check if the passed IP address is allowed to login.
     *
     * @param string $userIp the address to check
     * 
     * @return bool whether the passed IP address is not allowed to login
     */
    protected function isAllowedLogin(string $userIp)
    {
        $userIp = NetworkIp::fromStringIP($userIp);
        $allowedIPs = $this->getAllowedIPs();

        if (empty($allowedIPs)) {
            return false;
        }

        return $userIp->isInRanges($allowedIPs);
    }

    /**
     * Get the list of the allowed IPs/ranges.
     * 
     * @return array the list of the allowed IPs/ranges
     */
    protected function getAllowedIPs()
    {
        if (!is_array($this->pluginConfig['allow_login_from'] ?? null)) {
            return [];
        }

        $allowedIPs = array_map(function ($allowedIP) {
            return trim($allowedIP);
        }, $this->pluginConfig['allow_login_from']);

        return array_unique(array_values(array_filter($allowedIPs, function ($allowedIP) {
            return !empty($allowedIP);
        })));
    }

    /**
     * Check whether the unallowed IPs should be redirected.
     * 
     * @return bool whether the unallowed IPs should be redirected
     */
    protected function shouldRedirectUnallowedIPs()
    {
        if (!is_string($this->pluginConfig['redirect_unallowed_to'] ?? null)) {
            return false;
        }

        return false !== filter_var($this->pluginConfig['redirect_unallowed_to'], FILTER_VALIDATE_URL);
    }
}
