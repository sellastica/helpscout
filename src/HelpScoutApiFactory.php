<?php
namespace Sellastica\HelpScout;

class HelpScoutApiFactory
{
	/** @var string */
	private $apiKey;


	/**
	 * @param string $apiKey
	 */
	public function __construct(string $apiKey)
	{
		$this->apiKey = $apiKey;
	}

	/**
	 * @return \HelpScout\ApiClient
	 */
	public function create(): \HelpScout\ApiClient
	{
		$client = \HelpScout\ApiClient::getInstance();
		$client->setKey($this->apiKey);

		return $client;
	}
}