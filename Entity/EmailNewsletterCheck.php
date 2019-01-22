<?php

namespace FAC\EmailBundle\Entity;

use DateTime;
use Schema\Entity;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="`email_newsletter_check`")
 * @ORM\Entity(repositoryClass="FACFAC\EmailBundle\Repository\EmailNewsletterCheckRepository")
 * @UniqueEntity(
 *     fields="email",
 *     message="exist.email",
 * )
 */
class EmailNewsletterCheck extends Entity {

    /**
     * @ORM\Column(name="`id`", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer $id
     */
    private $id;

    /**
     * @ORM\Column(name="`id_profile`", type="string", length=255, nullable=true)
     */
    private $idProfile = null;

    /**
     * @ORM\Column(name="`email`", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @ORM\Column(name="`is_badged`", type="boolean", nullable=false, options={"default":0})
     */
    private $isBadged = false;

    /**
     * @ORM\Column(name="`created_on`", type="datetime", nullable=false)
     * @var DateTime $createdOn
     */
    private $createdOn;

    /**
     * @ORM\Column(name="`disabled_on`", type="datetime", nullable=true)
     * @var DateTime $disabledOn
     */
    private $disabledOn;

    /**
     * @ORM\Column(name="`is_disable`", type="boolean", nullable=false, options={"default":0})
     */
    private $isDisable = false;

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
     * EmailNewsletterCheck id
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
     * Set idProfile.
     *
     * @param string|null $idProfile
     *
     * @return EmailNewsletterCheck
     */
    public function setIdProfile($idProfile = null)
    {
        $this->idProfile = $idProfile;

        return $this;
    }

    /**
     * Get idProfile.
     *
     * @return string|null
     */
    public function getIdProfile()
    {
        return $this->idProfile;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return EmailNewsletterCheck
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
     * Set isBadged.
     *
     * @param bool $isBadged
     *
     * @return EmailNewsletterCheck
     */
    public function setIsBadged($isBadged)
    {
        $this->isBadged = $isBadged;

        return $this;
    }

    /**
     * Get isBadged.
     *
     * @return bool
     */
    public function getIsBadged()
    {
        return $this->isBadged;
    }

    /**
     * Set createdOn.
     *
     * @param \DateTime $createdOn
     *
     * @return EmailNewsletterCheck
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
     * Set disabledOn.
     *
     * @param \DateTime|null $disabledOn
     *
     * @return EmailNewsletterCheck
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
     * Set isDisable.
     *
     * @param bool $isDisable
     *
     * @return EmailNewsletterCheck
     */
    public function setIsDisable($isDisable)
    {
        $this->isDisable = $isDisable;

        return $this;
    }

    /**
     * Get isDisable.
     *
     * @return bool
     */
    public function getIsDisable()
    {
        return $this->isDisable;
    }
}
