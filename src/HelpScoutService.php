<?php
namespace Sellastica\HelpScout;

class HelpScoutService
{
	/** @var \HelpScout\ApiClient */
	private $helpScout;


	/**
	 * @param HelpScoutApiFactory $helpScoutApiFactory
	 */
	public function __construct(HelpScoutApiFactory $helpScoutApiFactory)
	{
		$this->helpScout = $helpScoutApiFactory->create();
	}

	/**
	 * @param int $mailboxId
	 * @param string $subject
	 * @param string $email
	 * @param string|null $firstName
	 * @param string|null $lastName
	 * @param string|null $phone
	 * @return \HelpScout\model\Conversation
	 */
	public function createConversation(
		int $mailboxId,
		string $subject,
		string $email,
		string $firstName = null,
		string $lastName = null,
		string $phone = null
	): \HelpScout\model\Conversation
	{
		//create ticket
		$conversation = new \HelpScout\model\Conversation();
		$conversation->setSubject($subject);
		$conversation->setType('email');
		$conversation->setStatus(\HelpScout\model\Conversation::STATUS_ACTIVE);

		//the mailbox associated with the conversation
		$conversation->setMailbox($this->helpScout->getMailboxProxy($mailboxId));

		$customer = $this->helpScout->getCustomerRefProxy(null, $email);
		$customer->setFirstName($firstName);
		$customer->setLastName($lastName);
		$customer->setPhone($phone);
		$conversation->setCustomer($customer);
		$conversation->setCreatedBy($customer);

		return $conversation;
	}

	/**
	 * @param \HelpScout\model\Conversation $conversation
	 * @param string $body
	 * @param bool $autoreply
	 */
	public function createMessageForSupport(
		\HelpScout\model\Conversation $conversation,
		string $body,
		bool $autoreply = false
	): void
	{
		$thread = new \HelpScout\model\thread\Customer();
		$thread->setBody($body);
		$thread->setCreatedBy($conversation->getCustomer());
		$thread->setStatus(\HelpScout\model\Conversation::STATUS_ACTIVE);

		if ($conversation->getId()) {
			$this->helpScout->createThread($conversation->getId(), $thread);
		} else {
			$conversation->addLineItem($thread);
			$this->helpScout->createConversation($conversation, false, $autoreply);
		}
	}
}