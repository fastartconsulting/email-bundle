<?php

namespace FAC\EmailBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Schema\Entity;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="`template_newsletters`")
 * @ORM\Entity(repositoryClass="FACFAC\EmailBundle\Repository\TemplateNewsletterRepository")
 */
class TemplateNewsletter extends Entity {

    /**
     * @ORM\Column(name="`id`", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer $id
     */
    private $id;

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
     */
    private $body = null;

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
     * @ORM\OneToMany(targetEntity="Newsletter", mappedBy="templateNewsletter")
     */
    private $newsletter;

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
            'subject'   => $this->serializedSubject(),
            'body'      => $this->serializedBody(),
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
     * TemplateNewsletter id
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
     * TemplateNewsletter subject
     * @JMS\VirtualProperty
     * @JMS\SerializedName("subject")
     * @JMS\Type("string")
     * @JMS\Groups({"view","list"})
     * @JMS\Since("1.0.x")
     */
    public function serializedSubject() {
        return (is_null($this->subject)?null:$this->subject);
    }

    /**
     * TemplateNewsletter body
     * @JMS\VirtualProperty
     * @JMS\SerializedName("body")
     * @JMS\Type("string")
     * @JMS\Groups({"view","list"})
     * @JMS\Since("1.0.x")
     */
    public function serializedBody() {
        return (is_null($this->body)?null:$this->body);
    }

    ################################################# GETTERS AND SETTERS FUNCTIONS

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->newsletter = new ArrayCollection();
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
     * Set subject.
     *
     * @param string $subject
     *
     * @return TemplateNewsletter
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
     * @return TemplateNewsletter
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
     * Set createdBy.
     *
     * @param int $createdBy
     *
     * @return TemplateNewsletter
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
     * @return TemplateNewsletter
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
     * @return TemplateNewsletter
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
     * @return TemplateNewsletter
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
     * @return TemplateNewsletter
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
     * @return TemplateNewsletter
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
     * @return TemplateNewsletter
     */
    public function setIsDisable($isDisable)
    {
        $this->isDisable = $isDisable;

        return $this;
    }

    /**
     * Add newsletter.
     *
     * @param \FAC\EmailBundle\Entity\Newsletter $newsletter
     *
     * @return TemplateNewsletter
     */
    public function addNewsletter(\FAC\EmailBundle\Entity\Newsletter $newsletter)
    {
        $this->newsletter[] = $newsletter;

        return $this;
    }

    /**
     * Remove newsletter.
     *
     * @param \FAC\EmailBundle\Entity\Newsletter $newsletter
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeNewsletter(\FAC\EmailBundle\Entity\Newsletter $newsletter)
    {
        return $this->newsletter->removeElement($newsletter);
    }

    /**
     * Get newsletter.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }
}
