<?php

require_once './vendor/autoload.php';

if (defined('PHPUNIT_COMPOSER_INSTALL')) {
	/**
	 * Custom config function for testing purposes
	 *
	 * @param string|array $key
	 * @param mixed|null $value
	 * @return mixed
	 */
	function config($key = null, $value = null)
	{
		return (new Illuminate\Config\Repository)->get($key, $value);
	}

	function now()
	{
		return new DateTime;
	}

	function public_path($value = null)
	{
		return __DIR__ . '/temp/' . $value;
	}

	function url($value = null)
	{
		return 'https://example.com/' . $value;
	}
}
