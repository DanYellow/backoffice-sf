<?php

namespace Backend\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;


use Backend\AdminBundle\Entity\Project as Project;

/**
 * GalleryItem
 * Tutorial : http://symfony.com/doc/current/cookbook/doctrine/file_uploads.html 
 *
 * @ORM\Table(name="gallery_items")
 * @ORM\Entity(repositoryClass="Backend\AdminBundle\Repository\GalleryItemRepository")
 * @ORM\HasLifecycleCallbacks
 */
class GalleryItem
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
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * 
     * @Assert\Image(
     *          maxSize = "6M", 
     *          maxSizeMessage = "Ce fichier est trop lourd ({{ size }}). La taille maximum autorisée est de : {{ limit }}",
     *          mimeTypes={"image/jpeg", "image/jpg", "image/png", "image/gif"},
     *          mimeTypesMessage= "Ce format n'est pas autorisé. Seul les images au format .jp(e)g, .png et .gif sont autorisés"
     * )
     */
    private $file;


    /**
     * @ORM\ManyToMany(targetEntity="Project", inversedBy="galleryItems")
     * @ORM\JoinTable(name="projects_galleryitems")
     */
    private $projects;



    /**
     * @var int
     *
     * @ORM\Column(name="order", type="integer")
     */
    private $order;


    private $temp;

    // Constructor
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }


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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return GalleryItem
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
     * Set path
     *
     * @param string $path
     *
     * @return GalleryItem
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return GalleryItem
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
     * Sets file
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;

        return $this;
    }  

    /**
     * Get file
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }  

    /**
     * Add project
     *
     * @param \Backend\AdminBundle\Entity\Project $project
     *
     * @return GalleryItem
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


    // Upload management
    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/gallery';
    }




    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues

        $finalFileName = null;
        $filename = null;

        if (null === $this->name) { 
            $this->name = $filename = pathinfo($this->getFile()->getClientOriginalName(), PATHINFO_FILENAME);   
        } else {
            $filename = $this->name;
        }

        $finalFileName = $filename . '-' . rand(0, 10000) . '.' . $this->getFile()->guessExtension();

        // move takes the target directory and then the
        // target filename to move to
        $this->getFile()->move(
            $this->getUploadRootDir(),
            $finalFileName
        );

        // set the path property to the filename where you've saved the file
        $this->path = $finalFileName;
  
        // clean up the file property as you won't need it anymore
        $this->file = null;
    }


    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->getFile()) {
            // do whatever you want to generate a unique name
            $filename = $this->getFile()->getClientOriginalName() . '-' . sha1(uniqid(mt_rand(), true));
            $this->path = $filename.'.'.$this->getFile()->guessExtension();
        }
    }


    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        $file = $this->getAbsolutePath();
        if ($file) {
            unlink($file);
        }
    }


    /**
     * Set order
     *
     * @param integer $order
     *
     * @return GalleryItem
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }
}
