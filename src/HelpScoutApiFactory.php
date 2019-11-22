<?php
namespace Sellastica\HelpScout;

class HelpScoutApiFactory
{
	/** @var string */
	private $appId;
	/** @var string */
	private $appSecret;


	/**
	 * @param string $appId
	 * @param string $appSecret
	 */
	public function __construct(string $appId, string $appSecret)
	{
		$this->appId = $appId;
		$this->appSecret = $appSecret;
	}

	/**
	 * @return \HelpScout\Api\ApiClient
	 */
	public function create(): \HelpScout\Api\ApiClient
	{
		$client = \HelpScout\Api\ApiClientFactory::createClient();
		$client->useClientCredentials($this->appId, $this->appSecret);

		return $client;
	}
}