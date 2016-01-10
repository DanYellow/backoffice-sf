<?php

namespace Backend\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Backend\AdminBundle\Entity\Project as Project;
use Backend\AdminBundle\Entity\GalleryItem as GalleryItem;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * GalleryItemOrder
 *
 * @ORM\Table(name="gallery_item_order", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQUE_GI_PROJECT", columns={"gallery_item_id", "project_id"})})
 * @ORM\Entity(repositoryClass="Backend\AdminBundle\Repository\GalleryItemOrderRepository")
 * @UniqueEntity({"project", "galleryItem"})
 */
class GalleryItemOrder
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
     * @var int
     *
     * @ORM\Column(name="orderInProject", type="smallint")
     */
    private $orderInProject;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *  @ORM\ManyToOne(targetEntity="Project", inversedBy="galleryItemsOrder", cascade={"detach", "persist"})
     */
    private $project;

    /**
     *  @ORM\ManyToOne(targetEntity="GalleryItem", inversedBy="galleryItemsOrder", cascade={"detach", "persist"})
     */
    private $galleryItem;

    /**
     * Set orderInProject
     *
     * @param integer $orderInProject
     *
     * @return GalleryItemOrder
     */
    public function setOrderInProject($orderInProject)
    {
        $this->orderInProject = $orderInProject;

        return $this;
    }

    /**
     * Get orderInProject
     *
     * @return int
     */
    public function getOrderInProject()
    {
        return $this->orderInProject;
    }

    /**
     * Set project
     *
     * @param \Backend\AdminBundle\Entity\Project $project
     *
     * @return GalleryItemOrder
     */
    public function setProject(\Backend\AdminBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \Backend\AdminBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set galleryItem
     *
     * @param \Backend\AdminBundle\Entity\GalleryItem $galleryItem
     *
     * @return GalleryItemOrder
     */
    public function setGalleryItem(\Backend\AdminBundle\Entity\GalleryItem $galleryItem = null)
    {
        $this->galleryItem = $galleryItem;

        return $this;
    }

    /**
     * Get galleryItem
     *
     * @return \Backend\AdminBundle\Entity\GalleryItem
     */
    public function getGalleryItem()
    {
        return $this->galleryItem;
    }
}
