<?php

namespace Backend\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DoctrineCommonCollectionsArrayCollection;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

use Backend\AdminBundle\Entity\GalleryItem as GalleryItem;
use Backend\AdminBundle\Entity\Category as Category;
use Backend\AdminBundle\Entity\GalleryItemOrder as GalleryItemOrder;


/**
 * Project
 *
 * @ORM\Table(name="projects")
 * @ORM\Entity(repositoryClass="Backend\AdminBundle\Repository\ProjectsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Project
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=100, nullable=false)
     * @Assert\NotBlank(message = "Veuillez entrer un titre")
     */
    private $title;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_online", type="boolean")
     */
    private $isOnline;


    /**
     * @ORM\OneToMany(targetEntity="GalleryItemOrder", mappedBy="project", cascade={"remove"})
     * @ORM\OrderBy({"orderInProject" = "ASC"})
     */
    private $galleryItemsOrder;

    /**
     * @ORM\ManyToOne(targetEntity="GalleryItem")
     * @ORM\JoinColumn(nullable=true)
     */
    private $sliderImage;


    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="projects")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;


    // Empty field for Gallery Items id. Not Stored only for forms
    private $galleryItemsId;


    // Empty field for slider image id. Not Stored only for forms
    private $sliderImageId;


    // Constructor
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->galleryItemsOrder = new ArrayCollection();
        $this->isOnline = false;
    }


    /**
     * @ORM\PostUpdate()
     */
    public function postUpdate(){
        $this->createdAt = new \DateTime();
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Project
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Project
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Project
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set isOnline
     *
     * @param \bool $isOnline
     *
     * @return Project
     */
    public function setIsOnline($isOnline)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    /**
     * Get isOnline
     *
     * @return \bool
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * Set category
     *
     * @param \Backend\AdminBundle\Entity\Category $category
     *
     * @return Project
     */
    public function setCategory(\Backend\AdminBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Backend\AdminBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    public function setGalleryItemsId($galleryItemsId)
    {        
        $this->galleryItemsId = $galleryItemsId;

        return $this;
    }

    public function getGalleryItemsId()
    {
        return $this->galleryItemsId;
    }

    public function setSliderImageId($sliderImageId)
    {        
        $this->sliderImageId = $sliderImageId;

        return $this;
    }

    public function getSliderImageId()
    {
        return $this->sliderImageId;
    }

    /**
     * Add galleryItemsOrder
     *
     * @param \Backend\AdminBundle\Entity\GalleryItemOrder $galleryItemsOrder
     *
     * @return Project
     */
    public function addGalleryItemsOrder(\Backend\AdminBundle\Entity\GalleryItemOrder $galleryItemsOrder)
    {
        $this->galleryItemsOrder[] = $galleryItemsOrder;

        return $this;
    }

    /**
     * Remove galleryItemsOrder
     *
     * @param \Backend\AdminBundle\Entity\GalleryItemOrder $galleryItemsOrder
     */
    public function removeGalleryItemsOrder(\Backend\AdminBundle\Entity\GalleryItemOrder $galleryItemsOrder)
    {
        $this->galleryItemsOrder->removeElement($galleryItemsOrder);
    }

    /**
     * Get galleryItemsOrder
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGalleryItemsOrder()
    {
        return $this->galleryItemsOrder;
    }

    /**
     * Set sliderImage
     *
     * @param \Backend\AdminBundle\Entity\GalleryItem $sliderImage
     *
     * @return Project
     */
    public function setSliderImage(\Backend\AdminBundle\Entity\GalleryItem $sliderImage = null)
    {
        $this->sliderImage = $sliderImage;

        return $this;
    }

    /**
     * Get sliderImage
     *
     * @return \Backend\AdminBundle\Entity\GalleryItem
     */
    public function getSliderImage()
    {
        return $this->sliderImage;
    }
}
