<?php

namespace Backend\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

use Backend\AdminBundle\Entity\Project as Project;

/**
 * User
 *
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="Backend\AdminBundle\Repository\CategoryRepository")
 */
class Category
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * 
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="slug_name", type="string", length=255, nullable=true)
     * Clean name of the category
     */
    protected $slugName;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Project", mappedBy="category", cascade={"persist", "merge"})
     */
    protected $projects;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct()
    {

    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slugName
     *
     * @param string $slugName
     *
     * @return Category
     */
    public function setSlugName($slugName)
    {
        $this->slugName = $slugName;

        return $this;
    }

    /**
     * Get slugName
     *
     * @return string
     */
    public function getSlugName()
    {
        return $this->slugName;
    }

    /**
     * Add project
     *
     * @param \Backend\AdminBundle\Entity\Project $project
     *
     * @return Category
     */
    public function addProject(\Backend\AdminBundle\Entity\Project $project)
    {
        $this->projects[] = $project;

        return $this;
    }

    /**
     * Remove project
     *
     * @param \Backend\AdminBundle\Entity\Project $project
     */
    public function removeProject(\Backend\AdminBundle\Entity\Project $project)
    {
        $this->projects->removeElement($project);
    }

    /**
     * Get projects
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProjects()
    {
        return $this->projects;
    }
}
