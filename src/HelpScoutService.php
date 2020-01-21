<?php
namespace Sellastica\HelpScout;

class HelpScoutService
{
	/** @var array */
	private $parameters;
	/** @var \HelpScout\Api\ApiClient */
	private $helpScout;


	/**
	 * @param array $parameters
	 * @param HelpScoutApiFactory $helpScoutApiFactory
	 */
	public function __construct(
		array $parameters,
		HelpScoutApiFactory $helpScoutApiFactory
	)
	{
		$this->parameters = $parameters;
		$this->helpScout = $helpScoutApiFactory->create();
	}

	/**
	 * @param int $mailboxId
	 * @param string $subject
	 * @param string $email
	 * @return \HelpScout\Api\Conversations\Conversation
	 */
	public function createConversation(
		int $mailboxId,
		string $subject,
		string $email
	): \HelpScout\Api\Conversations\Conversation
	{
		$conversation = new \HelpScout\Api\Conversations\Conversation();
		$conversation->setSubject($subject);
		$conversation->setType('email');
		$conversation->setStatus(\HelpScout\Api\Conversations\Conversation::STATUS_ACTIVE);
		$conversation->setMailboxId($mailboxId);

		$customer = new \HelpScout\Api\Customers\Customer();
		$customer->addEmail($email);
		$conversation->setCustomer($customer);
		$conversation->setCreatedByCustomer($customer);

		return $conversation;
	}

	/**
	 * @param \HelpScout\Api\Conversations\Conversation $conversation
	 * @param string $body
	 * @throws \HelpScout\Api\Exception\Exception
	 */
	public function createMessageForSupport(
		\HelpScout\Api\Conversations\Conversation $conversation,
		string $body
	): void
	{
		$customer = $conversation->getCustomer();
		$this->clearCustomerFields($customer);

		$thread = new \HelpScout\Api\Conversations\Threads\CustomerThread();
		$thread->setCustomer($customer);
		$thread->setText($body);
		$thread->setCreatedByCustomer($customer);

		$this->createThread($conversation, $thread);
	}

	/**
	 * @param \HelpScout\Api\Conversations\Conversation $conversation
	 * @param string $body
	 */
	public function createMessageForCustomer(
		\HelpScout\Api\Conversations\Conversation $conversation,
		string $body
	): void
	{
		$user = new \HelpScout\Api\Users\User();
		$user->setId($this->parameters['user_id']);

		$customer = $conversation->getCustomer();
		$this->clearCustomerFields($customer);

		$thread = new \HelpScout\Api\Conversations\Threads\ReplyThread();
		$thread->setCustomer($customer);
		$thread->setText($body);
		$thread->setCreatedByUser($user);

		$this->createThread($conversation, $thread);
	}

	/**
	 * @param \HelpScout\Api\Conversations\Conversation $conversation
	 * @param \HelpScout\Api\Conversations\Threads\Thread $thread
	 */
	private function createThread(
		\HelpScout\Api\Conversations\Conversation $conversation,
		\HelpScout\Api\Conversations\Threads\Thread $thread
	): void
	{
		if (!$conversation->getId()) {
			$conversation->addThread($thread);
			$conversationId = $this->helpScout->conversations()->create($conversation);
			$conversation->setId($conversationId);
		} else {
			$this->helpScout->threads()
				->create($conversation->getId(), $thread);
		}
	}

	/**
	 * @param int $conversationId
	 */
	public function deleteConversation(int $conversationId): void
	{
		try {
			$this->helpScout->conversations()->delete($conversationId);
		} catch (\HelpScout\Api\Exception\Exception | \GuzzleHttp\Exception\ServerException $e) {
		}
	}

	/**
	 * @param int $conversationId
	 * @return string|null
	 */
	public function getCustomerEmail(int $conversationId): ?string
	{
		try {
			$conversation = $this->helpScout->conversations()->get($conversationId);
			if ($conversation->getCustomer()) {
				return $conversation->getCustomer()->getFirstEmail();
			}
		} catch (\HelpScout\Api\Exception\Exception $e) {
		}

		return null;
	}

	/**
	 * @param \HelpScout\Api\Customers\Customer $customer
	 */
	private function clearCustomerFields(\HelpScout\Api\Customers\Customer $customer): void
	{
		$customer->setGender(null);
		$customer->setJobTitle(null);
		$customer->setLocation(null);
		$customer->setOrganization(null);
		$customer->setPhotoType(null);
		$customer->setPhotoUrl(null);
		$customer->setBackground(null);
	}
}