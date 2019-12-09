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
	 * @param string|null $firstName
	 * @param string|null $lastName
	 * @param string|null $phone
	 * @return \HelpScout\Api\Conversations\Conversation
	 */
	public function createConversation(
		int $mailboxId,
		string $subject,
		string $email,
		string $firstName = null,
		string $lastName = null,
		string $phone = null
	): \HelpScout\Api\Conversations\Conversation
	{
		$conversation = new \HelpScout\Api\Conversations\Conversation();
		$conversation->setSubject($subject);
		$conversation->setType('email');
		$conversation->setStatus(\HelpScout\Api\Conversations\Conversation::STATUS_ACTIVE);
		$conversation->setMailboxId($mailboxId);

		$customer = new \HelpScout\Api\Customers\Customer();
		$customer->setFirstName($firstName);
		$customer->setLastName($lastName);
		$customer->addEmail($email);
		if ($phone) {
			$customer->addPhone($phone);
		}

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
		$thread = new \HelpScout\Api\Conversations\Threads\CustomerThread();
		$thread->setCustomer($conversation->getCustomer());
		$thread->setText($body);
		$thread->setCreatedByCustomer($conversation->getCustomer());

		$conversation->addThread($thread);
		if (!$conversation->getId()) {
			$this->helpScout->conversations()->create($conversation);
		}
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

		$thread = new \HelpScout\Api\Conversations\Threads\CustomerThread();
		$thread->setText($body);
		$thread->setCreatedByUser($user);

		$conversation->addThread($thread);
		$this->helpScout->conversations()->create($conversation);
	}
}