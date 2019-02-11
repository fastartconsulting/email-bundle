<?php

namespace FAC\EmailBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use FAC\UserBundle\Entity\User;

/**
 * @ORM\Table(name="`emails`")
 * @ORM\Entity(repositoryClass="FAC\EmailBundle\Repository\EmailRepository")
 */
class Email {

    const TYPE_REGISTRATION_CONFIRM         = 0;
    const TYPE_REGISTRATION_SUCCESS         = 1;
    const TYPE_PASSWORD_RESETTING           = 2;
    const TYPE_PASSWORD_RESETTING_SUCCESS   = 3;

    const STATUS_PENDING    = 0;
    const STATUS_SENDED     = 1;
    const STATUS_FAILED     = 2;

    /**
     * @ORM\Column(name="`id`", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer $id
     */
    private $id;

    /**
     * @ORM\Column(name="`recipient`", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message = "require.email")
     * @Assert\Email(
     *     message = "invalid.email",
     *     checkMX = true,
     *     checkHost = true
     * )
     */
    private $recipient = null;

    /**
     * @ORM\ManyToOne(targetEntity="FAC\UserBundle\Entity\User", inversedBy="emails")
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id", nullable=true)
     * @var User $user
     */
    private $user;

    /**
     * @ORM\Column(name="`subject`", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message = "require.subject")
     * @Assert\Regex(
     *     pattern="/^[^<>]*$/",
     *     message="invalid.subject"
     * )
     * @Assert\Length(
     *      min = 0,
     *      max = 255,
     *      maxMessage = "max.length.subject"
     * )
     */
    private $subject = null;

    /**
     * @ORM\Column(name="`body`", type="text", nullable=false)
     * @Assert\NotBlank(message="require.body")
     * @Assert\Regex(
     *     pattern="/^[^<>]*$/",
     *     message="invalid.body"
     * )
     */
    private $body = null;

    /**
     * @ORM\Column(name="`queue_on`", type="datetime", nullable=false)
     * @var DateTime $queueOn
     */
    private $queueOn;

    /**
     * @ORM\Column(name="`send_on`", type="datetime", nullable=true, options={"default":NULL})
     * @var DateTime $sendOn
     */
    private $sendOn;

    /**
     * @ORM\Column(name="`failed_on`", type="datetime", nullable=true, options={"default":NULL})
     * @var DateTime $failedOn
     */
    private $failedOn;

    /**
     * @ORM\Column(name="`status`", type="integer", nullable=true, options={"default":0})
     */
    private $status;

    ################################################# GETTERS AND SETTERS FUNCTIONS


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set recipient.
     *
     * @param string $recipient
     *
     * @return Email
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Get recipient.
     *
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Set subject.
     *
     * @param string $subject
     *
     * @return Email
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set body.
     *
     * @param string $body
     *
     * @return Email
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set queueOn.
     *
     * @param \DateTime $queueOn
     *
     * @return Email
     */
    public function setQueueOn($queueOn)
    {
        $this->queueOn = $queueOn;

        return $this;
    }

    /**
     * Get queueOn.
     *
     * @return \DateTime
     */
    public function getQueueOn()
    {
        return $this->queueOn;
    }

    /**
     * Set sendOn.
     *
     * @param \DateTime|null $sendOn
     *
     * @return Email
     */
    public function setSendOn($sendOn = null)
    {
        $this->sendOn = $sendOn;

        return $this;
    }

    /**
     * Get sendOn.
     *
     * @return \DateTime|null
     */
    public function getSendOn()
    {
        return $this->sendOn;
    }

    /**
     * Set failedOn.
     *
     * @param \DateTime|null $failedOn
     *
     * @return Email
     */
    public function setFailedOn($failedOn = null)
    {
        $this->failedOn = $failedOn;

        return $this;
    }

    /**
     * Get failedOn.
     *
     * @return \DateTime|null
     */
    public function getFailedOn()
    {
        return $this->failedOn;
    }

    /**
     * Set status.
     *
     * @param integer $status
     *
     * @return Email
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return integer|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set user.
     *
     * @param \FAC\UserBundle\Entity\User|null $user
     *
     * @return Email
     */
    public function setUser(\FAC\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \FAC\UserBundle\Entity\User|null
     */
    public function getUser()
    {
        return $this->user;
    }
}
