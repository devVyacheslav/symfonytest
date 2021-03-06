<?php

namespace MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use MainBundle\Entity\Traits\StateDateTrait;
use MainBundle\Entity\Security\Group;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="MainBundle\Repository\UserRepository")
 */
class User extends BaseEntity implements UserInterface
{
    use StateDateTrait;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $login;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    private $pd_processing_accepted;

    /**
     * @ORM\ManyToMany(targetEntity="MainBundle\Entity\Security\Group")
     * @ORM\JoinTable(name="users_groups",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     *      )
     */
    private $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    /**
     * @param $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $login
     * @return User
     */
    public function setUsername($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->login;
    }

    /**
     * @param $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return null;
    }

    /**
     * @param ArrayCollection $groups
     */
    public function setGroups(ArrayCollection $groups)
    {
        $this->groups = $groups;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $codes = [];

        foreach ($this->getGroupObjects() as $group) {
            foreach ($group->getRoles() as $role) {
                $codes[] = $role->getRole();
            }
        }

        return array_unique($codes);
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        $groups = $this->getGroupObjects()->map(function(Group $group) {
            return $group->getCode();
        });

        return $groups->toArray();
    }

    public function eraseCredentials()
    {
        return null;
    }

    /**
     * @return ArrayCollection
     */
    public function getGroupObjects()
    {
        return $this->groups;
    }

    /**
     * @return string
     */
    public function isPdProcessingAccepted()
    {
        return $this->pd_processing_accepted == self::VALUE_YES;
    }

    /**
     * @param $pd_processing_accepted
     * @return User
     */
    public function setPdProcessingAccepted($pd_processing_accepted)
    {
        $this->pd_processing_accepted = $pd_processing_accepted ? self::VALUE_YES : self::VALUE_NO;

        return $this;
    }

    /**
     * Set login
     *
     * @param string $login
     *
     * @return User
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Get pdProcessingAccepted
     *
     * @return string
     */
    public function getPdProcessingAccepted()
    {
        return $this->pd_processing_accepted;
    }

    /**
     * Add group
     *
     * @param \MainBundle\Entity\Security\Group $group
     *
     * @return User
     */
    public function addGroup(\MainBundle\Entity\Security\Group $group)
    {
        $this->groups[] = $group;

        return $this;
    }

    /**
     * Remove group
     *
     * @param \MainBundle\Entity\Security\Group $group
     */
    public function removeGroup(\MainBundle\Entity\Security\Group $group)
    {
        $this->groups->removeElement($group);
    }
}
