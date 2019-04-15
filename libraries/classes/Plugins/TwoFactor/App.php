<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Second authentication factor handling
 *
 * @package PhpMyAdmin
 */
namespace PhpMyAdmin\Plugins\TwoFactor;

use PhpMyAdmin\Plugins\TwoFactorPlugin;
use PhpMyAdmin\Template;

/**
 * Simple two-factor authentication using a 3rd party website
 */
class App extends TwoFactorPlugin
{
    /**
     * @var string
     */
    public static $id = 'app';

    /**
     * Checks authentication, returns true on success
     *
     * @return boolean
     */
    public function check()
    {
        global $cfg;
        $this->_provided = true;
        if(!isset($cfg['App2FA']['url']) || !isset($cfg['App2FA']['cookie']))
        {
            $this->_message = __('Invalid App2FA config');
            return false;
        }

        $data = http_build_query(['c' => $_COOKIE[$cfg['App2FA']['cookie']]]);
        $opts = [
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false
            ]
        ];
        $context  = stream_context_create($opts);
        $ret = \file_get_contents($cfg['App2FA']['url'] .'&'. $data, false, $context);
        if(!$ret || !($ret = json_decode($ret, true)) || !$ret['status'])
        {
            $this->_message = __('App2FA failed');
            return false;
        }

        return true;
    }

    /**
     * Performs backend configuration
     *
     * @return boolean
     */
    public function configure()
    {
        $this->_twofactor->config['settings']['init'] = true;
        return true;
    }

    /**
     * Renders user interface to enter two-factor authentication
     *
     * @return string HTML code
     */
    public function render()
    {
        return Template::get('login/twofactor/simple')->render();
    }

    /**
     * Get user visible name
     *
     * @return string
     */
    public static function getName()
    {
        return __('App two-factor authentication');
    }

    /**
     * Get user visible description
     *
     * @return string
     */
    public static function getDescription()
    {
        return __('Provides authentication using another website!');
    }
}
