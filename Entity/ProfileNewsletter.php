<?php

namespace FAC\EmailBundle\Entity;

use DateTime;
use Schema\Entity;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="`profile_newsletters`")
 * @ORM\Entity(repositoryClass="FACFAC\EmailBundle\Repository\ProfileNewsletterRepository")
 */
class ProfileNewsletter extends Entity {

    /**
     * @ORM\Column(name="`id`", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer $id
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Newsletter", inversedBy="profileNewsletter")
     * @ORM\JoinColumn(name="id_newsletters", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(message = "require.newsletter")
     */
    private $newsletter;

    /**
     * @ORM\Column(name="`id_profile`", type="string", length=255, nullable=true)
     */
    private $idProfile = null;

    /**
     * @ORM\Column(name="`email`", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(name="`created_on`", type="datetime", nullable=false)
     * @var DateTime $createdOn
     */
    private $createdOn;

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
     * Set newsletter.
     *
     * @param Newsletter|null $newsletter
     *
     * @return ProfileNewsletter
     */
    public function setNewsletter(Newsletter $newsletter = null)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * Get newsletter.
     *
     * @return Newsletter|null
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * @return mixed
     */
    public function getIdProfile()
    {
        return $this->idProfile;
    }

    /**
     * @param mixed $idProfile
     */
    public function setIdProfile($idProfile): void
    {
        $this->idProfile = $idProfile;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return ProfileNewsletter
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set createdOn.
     *
     * @param \DateTime $createdOn
     *
     * @return ProfileNewsletter
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
}
