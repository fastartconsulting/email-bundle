<?php

namespace FAC\EmailBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Schema\Entity;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="`newsletters`")
 * @ORM\Entity(repositoryClass="FACFAC\EmailBundle\Repository\NewsletterRepository")
 */
class Newsletter extends Entity {

    const MAILING_LIST_ALL              = 0;
    const MAILING_LIST_PATIENTS         = 1;
    const MAILING_LIST_BADGES           = 2;
    const MAILING_LIST_SPECIFIC_BADGE   = 3;

    /**
     * @ORM\Column(name="`id`", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer $id
     */
    private $id;

    /**
     * @ORM\Column(name="`summary`", type="string", length=255, nullable=true)
     * @Assert\Regex(
     *     pattern="/^[^<>]*$/",
     *     message="invalid.summary"
     * )
     * @Assert\Length(
     *      min = 0,
     *      max = 255,
     *      maxMessage = "max.length.summary"
     * )
     */
    private $summary = null;

    /**
     * @ORM\Column(name="`is_draft`", type="boolean", nullable=false, options={"default":0})
     */
    private $isDraft = false;

    /**
     * @ORM\Column(name="`send_now`", type="boolean", nullable=true, options={"default":0})
     */
    private $sendNow = false;

    /**
     * @ORM\Column(name="`mailing_list_type`", type="integer", nullable=false)
     * @Assert\NotBlank(message = "require.mailing.list.type")
     */
    private $mailingListType;

    /**
     * @ORM\Column(name="`id_badge_structure_mailing_list`", type="integer", nullable=true)
     */
    private $idBadgeStructureMailingList;

    /**
     * @ORM\Column(name="`send_on`", type="datetime", nullable=true, options={"default":NULL})
     * @var DateTime $sendOn
     */
    private $sendOn;

    /**
     * @ORM\Column(name="`created_by`", type="integer", nullable=false)
     */
    private $createdBy;

    /**
     * @ORM\Column(name="`created_on`", type="datetime", nullable=false)
     * @var DateTime $createdOn
     */
    private $createdOn;

    /**
     * @ORM\Column(name="`edited_by`", type="integer", nullable=true)
     */
    private $editedBy;

    /**
     * @ORM\Column(name="`edited_on`", type="datetime", nullable=true)
     * @var DateTime $editedOn
     */
    private $editedOn;

    /**
     * @ORM\Column(name="`disabled_by`", type="integer", nullable=true)
     */
    private $disabledBy;

    /**
     * @ORM\Column(name="`disabled_on`", type="datetime", nullable=true)
     * @var DateTime $disabledOn
     */
    private $disabledOn;

    /**
     * @ORM\Column(name="`is_disable`", type="boolean", nullable=false, options={"default":0})
     */
    private $isDisable = false;

    /**
     * @ORM\Column(name="`queuing`", type="boolean", nullable=false, options={"default":0})
     */
    private $queuing = false;

    /**
     * @ORM\ManyToOne(targetEntity="TemplateNewsletter", inversedBy="newsletter")
     * @ORM\JoinColumn(name="id_template_newsletters", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message = "require.template.newsletter")
     */
    private $templateNewsletter;

    /**
     * @ORM\OneToMany(targetEntity="ProfileNewsletter", mappedBy="newsletter")
     */
    private $profileNewsletter;

    ################################################# SERIALIZER FUNCTIONS

    /**
     * Returns the array of fields to serialize in entity administration view.
     * @return array
     */
    public function adminSerializer()
    {
        $view_vars = $this->viewSerializer();

        $admin_vars = array(
            'id'        => $this->serializedId(),
            'summary'   => $this->serializedSummary(),
            'sendOn'    => $this->serializedSendOn(),
            'isDraft'   => $this->serializedIsDraft()
        );

        return array_merge($view_vars, $admin_vars);
    }

    /**
     * Returns the array of fields to serialize in entity view.
     * @return array
     */
    public function viewSerializer()
    {
        $list_vars = $this->listSerializer();

        $view_vars = array(
        );

        return array_merge($list_vars, $view_vars);
    }

    /**
     * Returns the array of fields to serialize in a list of this entity.
     * @return array
     */
    public function listSerializer()
    {
        $list_vars = array(
        );
        return $list_vars;
    }

    /**
     * Returns the hash code unique identifier of the entity.
     * @return string
     */
    public function hashCode()
    {
        // TODO: Implement hashCode() method.
    }

    ################################################# SERIALIZED FUNCTIONS

    /**
     * Newsletter id
     * @JMS\VirtualProperty
     * @JMS\SerializedName("id")
     * @JMS\Type("string")
     * @JMS\Groups({"view","list"})
     * @JMS\Since("1.0.x")
     */
    public function serializedId() {
        return (is_null($this->id)?null:$this->id);
    }

    /**
     * Newsletter summary
     * @JMS\VirtualProperty
     * @JMS\SerializedName("summary")
     * @JMS\Type("string")
     * @JMS\Groups({"view","list"})
     * @JMS\Since("1.0.x")
     */
    public function serializedSummary() {
        return (is_null($this->summary)?null:$this->summary);
    }

    /**
     * Newsletter isDraft
     * @JMS\VirtualProperty
     * @JMS\SerializedName("isDraft")
     * @JMS\Type("string")
     * @JMS\Groups({"view","list"})
     * @JMS\Since("1.0.x")
     */
    public function serializedIsDraft() {
        return (is_null($this->isDraft)?null:$this->isDraft);
    }

    /**
     * Newsletter sendOn
     * @JMS\VirtualProperty
     * @JMS\SerializedName("createdOn")
     * @JMS\Type("string")
     * @JMS\Groups({"view","list"})
     * @JMS\Since("1.0.x")
     */
    public function serializedSendOn() {
        return (is_null($this->sendOn)?null:strftime('%Y-%m-%d %H:%M',$this->sendOn->getTimestamp()));
    }

    ################################################# GETTERS AND SETTERS FUNCTIONS

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->profileNewsletter = new ArrayCollection();
    }

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
     * Set summary.
     *
     * @param string|null $summary
     *
     * @return Newsletter
     */
    public function setSummary($summary = null)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary.
     *
     * @return string|null
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set sendOn.
     *
     * @param \DateTime|null $sendOn
     *
     * @return Newsletter
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
     * Set templateNewsletter.
     *
     * @param TemplateNewsletter|null $templateNewsletter
     *
     * @return Newsletter
     */
    public function setTemplateNewsletter(TemplateNewsletter $templateNewsletter = null)
    {
        $this->templateNewsletter = $templateNewsletter;

        return $this;
    }

    /**
     * Get templateNewsletter.
     *
     * @return TemplateNewsletter|null
     */
    public function getTemplateNewsletter()
    {
        return $this->templateNewsletter;
    }

    /**
     * Set createdBy.
     *
     * @param int $createdBy
     *
     * @return Newsletter
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy.
     *
     * @return int
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set createdOn.
     *
     * @param \DateTime $createdOn
     *
     * @return Newsletter
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    /**
     * Get createdOn.
     *
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * Set editedBy.
     *
     * @param int|null $editedBy
     *
     * @return Newsletter
     */
    public function setEditedBy($editedBy = null)
    {
        $this->editedBy = $editedBy;

        return $this;
    }

    /**
     * Get editedBy.
     *
     * @return int|null
     */
    public function getEditedBy()
    {
        return $this->editedBy;
    }

    /**
     * Set editedOn.
     *
     * @param \DateTime|null $editedOn
     *
     * @return Newsletter
     */
    public function setEditedOn($editedOn = null)
    {
        $this->editedOn = $editedOn;

        return $this;
    }

    /**
     * Get editedOn.
     *
     * @return \DateTime|null
     */
    public function getEditedOn()
    {
        return $this->editedOn;
    }

    /**
     * Set disabledBy.
     *
     * @param int|null $disabledBy
     *
     * @return Newsletter
     */
    public function setDisabledBy($disabledBy = null)
    {
        $this->disabledBy = $disabledBy;

        return $this;
    }

    /**
     * Get disabledBy.
     *
     * @return int|null
     */
    public function getDisabledBy()
    {
        return $this->disabledBy;
    }

    /**
     * Set disabledOn.
     *
     * @param \DateTime|null $disabledOn
     *
     * @return Newsletter
     */
    public function setDisabledOn($disabledOn = null)
    {
        $this->disabledOn = $disabledOn;

        return $this;
    }

    /**
     * Get disabledOn.
     *
     * @return \DateTime|null
     */
    public function getDisabledOn()
    {
        return $this->disabledOn;
    }

    /**
     * @return mixed
     */
    public function getIsDisable()
    {
        return $this->isDisable;
    }

    /**
     * @param mixed $isDisable
     * @return Newsletter
     */
    public function setIsDisable($isDisable)
    {
        $this->isDisable = $isDisable;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsDraft()
    {
        return $this->isDraft;
    }

    /**
     * @param mixed $isDraft
     * @return Newsletter
     */
    public function setIsDraft($isDraft)
    {
        $this->isDraft = $isDraft;

        return $this;
    }

    /**
     * Add profileNewsletter.
     *
     * @param \FAC\EmailBundle\Entity\ProfileNewsletter $profileNewsletter
     *
     * @return Newsletter
     */
    public function addProfileNewsletter(\FAC\EmailBundle\Entity\ProfileNewsletter $profileNewsletter)
    {
        $this->profileNewsletter[] = $profileNewsletter;

        return $this;
    }

    /**
     * Remove profileNewsletter.
     *
     * @param \FAC\EmailBundle\Entity\ProfileNewsletter $profileNewsletter
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProfileNewsletter(\FAC\EmailBundle\Entity\ProfileNewsletter $profileNewsletter)
    {
        return $this->profileNewsletter->removeElement($profileNewsletter);
    }

    /**
     * Get profileProfileNewsletter.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProfileNewsletter()
    {
        return $this->profileNewsletter;
    }

    /**
     * Set mailingListType.
     *
     * @param int $mailingListType
     *
     * @return Newsletter
     */
    public function setMailingListType($mailingListType)
    {
        $this->mailingListType = $mailingListType;

        return $this;
    }

    /**
     * Get mailingListType.
     *
     * @return int
     */
    public function getMailingListType()
    {
        return $this->mailingListType;
    }

    /**
     * Set idBadgeStructureMailingList.
     *
     * @param int|null $idBadgeStructureMailingList
     *
     * @return Newsletter
     */
    public function setIdBadgeStructureMailingList($idBadgeStructureMailingList = null)
    {
        $this->idBadgeStructureMailingList = $idBadgeStructureMailingList;

        return $this;
    }

    /**
     * Get idBadgeStructureMailingList.
     *
     * @return int|null
     */
    public function getIdBadgeStructureMailingList()
    {
        return $this->idBadgeStructureMailingList;
    }

    /**
     * Set sendNow.
     *
     * @param bool|null $sendNow
     *
     * @return Newsletter
     */
    public function setSendNow($sendNow = null)
    {
        $this->sendNow = $sendNow;

        return $this;
    }

    /**
     * Get sendNow.
     *
     * @return bool|null
     */
    public function getSendNow()
    {
        return $this->sendNow;
    }

    /**
     * Set queuing.
     *
     * @param bool $queuing
     *
     * @return Newsletter
     */
    public function setQueuing($queuing)
    {
        $this->queuing = $queuing;

        return $this;
    }

    /**
     * Get queuing.
     *
     * @return bool
     */
    public function getQueuing()
    {
        return $this->queuing;
    }
}
