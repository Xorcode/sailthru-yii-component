<?php
/**
 * Sailthru CApplicationComponent class
 * 
 * Wrapper for the Sailthru PHP5 SDK
 * https://github.com/sailthru/sailthru-php5-client
 * 
 * Copyright (C) 2014 Torgny Bjers
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/gpl-3.0.txt>.
 * 
 * @author Torgny Bjers <torgny@xorcode.com>
 * @package application.extensions.sailthru
 * @version 1.0
 */

class Sailthru extends CApplicationComponent
{
    public $config = array();
    public $api_key;
    public $api_secret;

    private $_sailthruApi;

    public function getConfig()
    {
        if (Yii::app()->hasComponent('cache')) {
            $config = Yii::app()->cache->get('Sailthru.config');
        }
        if (!isset($config) || !is_array($config)) {
            $config = array();
            foreach ($this->config as $configElem => $value) {
                $config[$configElem] = $value;
            }
            if (Yii::app()->hasComponent('cache')) {
                Yii::app()->cache->set('Sailthru.config', $config);
            }
        }
        return $config;
    }

    /**
     * Return a Sailthru_Client singleton instance
     * 
     * @throws CException if the Sailthru Web PHP SDK cannot be loaded
     * @return instance of Sailthru PHP SDK class
     */
    protected function _getSailthruApi()
    {        
        if (is_null($this->_sailthruApi)) {
            if ($this->api_key && $this->api_secret) {            
                $this->_sailthruApi = new Sailthru_Client($this->api_key, $this->api_secret);
            } else {
                if (!$this->api_key) {
                    throw new SailthruException('Sailthru API key not specified.');
                } else if (!$this->api_secret) {
                    throw new SailthruException('Sailthru API secret not specified.');
                }
            }
        }
        if (!is_object($this->_sailthruApi)) {
            throw new SailthruException('Sailthru SDK could not be initialized.');
        }
        return $this->_sailthruApi;
    }    

    /**
     * Forward calls to methods on the Sailthru Client
     * 
     * @param  string $name
     * @param  array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->_getSailthruApi(), $name)) {
            return call_user_func_array(array($this->_getSailthruApi(), $name), $arguments);
        } else {
            throw new CException('No such method, ' . $name . ', in the Sailthru SDK');
        }
    }
}

/**
 * The SailthruException exception class.
 * 
 * @author Torgny Bjers <torgny@xorcode.com>
 * @package application.extensions.sailthru
 * @version 1.0
 */
class SailthruException extends CException {}
