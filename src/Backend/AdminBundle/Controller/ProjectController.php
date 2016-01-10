<?php

namespace Backend\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


use Backend\AdminBundle\Entity\Project;
use Backend\AdminBundle\Entity\GalleryItemOrder;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Validator\Constraints\NotBlank;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;


use BackendAdminBundle\Form\Type\GalleryItemType;


class ProjectController extends Controller
{

  private $itemsPerPage = 16;
  private $isOnlineChoices = array(true => 'Oui', false => 'Non');

  # @returns content of gallery in json format
  private function getGalleryItems() {
    $encoders = array(new XmlEncoder(), new JsonEncoder());
    $normalizer = new ObjectNormalizer();

    $normalizer->setCircularReferenceHandler(function ($object) {
      return $object->getId();
    });    

    $serializer = new Serializer(array($normalizer), $encoders);

    $em = $this->getDoctrine()->getManager();
    $galleryItems = $em->getRepository('BackendAdminBundle:GalleryItem')->findAll();
    return $galleryItems = $serializer->serialize($galleryItems, 'json');
  }

  # @returns a string of every gallery items contained in the project
  private function getGalleryItemId($project) {
    $ids = [];
    foreach ($project->getGalleryItemsOrder() as $galleryItem) {
      $ids[] = $galleryItem->getGalleryItem()->getId();
    }
    
    return implode(",", $ids);
  }


  ## Public methods

  public function projectGalleryAction($page = 1, $category = null)
  {
    $em = $this->getDoctrine()->getManager();

    $dqlCategories = 'SELECT a FROM BackendAdminBundle:Category a ORDER BY a.name ASC';
    $categories    = $em->createQuery($dqlCategories)
                        ->getResult();

    $query = $em->createQueryBuilder('p')
                ->select('p')
                ->from('BackendAdminBundle:Project', 'p')
                ->leftJoin('p.category', 'c');

    $nbProjects = count($query->getQuery()->getResult());
    $nbPages = ceil($nbProjects / $this->itemsPerPage);

    if ($category !== null && $category !== "all") {
      $query->andWhere('c.name = :categoryName')
            ->setParameters(array('categoryName' => $category));
    }
    
    $projectsQuery = $query->setFirstResult(($page * $this->itemsPerPage) - $this->itemsPerPage)
                           ->setMaxResults($this->itemsPerPage)->getQuery();
    $projects = $projectsQuery->getResult();

    
    if ($nbProjects == 0)
      $nbPages = 1;

    return $this->render('BackendAdminBundle:Projects:projects-gallery.html.twig', 
        array(
            'projects' => $projects,
            'categories' => $categories,
            'nbPages' => $nbPages
        ) 
    );
  }

  public function addProjectAction(Request $request)
  {
    $project = new Project();

    $form = $this->createFormBuilder($project)
        ->add('title')
        ->add('isOnline', ChoiceType::class,array(
             'choices' => $this->isOnlineChoices,
             'expanded' => true,
             'multiple' => false,
             'data' => 0
        ))
        ->add('category', EntityType::class, array(
                            'class' => 'BackendAdminBundle:Category',
                            'choice_label' => 'name',
                        ))
        ->add('description')
        ->add('galleryItemsId', HiddenType::class, 
                array('attr' => array(
                  'id' => 'perojectImagesId'
                )))
        ->add('save', SubmitType::class,
                    array('label' => 'Ajouter')
                    )
        ->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
      $galleryItemsIdArray = array_filter(explode(',', $project->getGalleryItemsId()));

      $em = $this->getDoctrine()->getManager();
      
      foreach ($galleryItemsIdArray as $index=>$id) {
        $galleryItem = $em->find('Backend\AdminBundle\Entity\GalleryItem', $id);

        $galleryItemOrder = new GalleryItemOrder();

        $galleryItemOrder->setGalleryItem($galleryItem);
        $galleryItemOrder->setProject($project);
        $galleryItemOrder->setOrderInProject($index);

        $em->persist($galleryItemOrder);
        $em->flush();
      }

      $em->persist($project);
      $em->flush();

      $request->getSession()
              ->getFlashBag()
              ->add('successMessage', 'Le projet a été ajouté !');

      $dql   = 'SELECT a FROM BackendAdminBundle:Project a';
      $nbProjects = count($em->createQuery($dql)->getResult());
      $lastPage = ceil($nbProjects / $this->itemsPerPage);

      return $this->redirectToRoute('backend_admin_projects_display', array('page' => $lastPage), 301);
    }

    
    $uploadFolder = "uploads/gallery/";

    return $this->render('BackendAdminBundle:Projects:add-project.html.twig', 
                array(
                    'form' => $form->createView(),
                    'galleryItems' => $this->getGalleryItems(),
                    'uploadFolder' => $uploadFolder,
                ) 
            );
  } 

  public function updateProjectAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $project = $em->find('Backend\AdminBundle\Entity\Project', $id);
    
    $form = $this->createFormBuilder($project)
        ->add('title')
        ->add('isOnline', ChoiceType::class,array(
             'choices' => $this->isOnlineChoices,
             'expanded' => true,
             'multiple' => false,
             'data' => (int)$project->getIsOnline()
        ))
        ->add('category', EntityType::class, array(
                            'class' => 'BackendAdminBundle:Category',
                            'choice_label' => 'name',
                        ))
        ->add('description')
        ->add('galleryItemsId', HiddenType::class, 
                array('attr' => array(
                  'id' => "projectImagesId",
                  'value' => $this->getGalleryItemId($project)
                )))
        ->add('save', SubmitType::class,
                array(
                'label' => 'Mettre à jour'
                ))
        ->getForm();

    $form->handleRequest($request);
    
    // http://future500.nl/articles/2013/09/doctrine-2-how-to-handle-join-tables-with-extra-columns/
    if ($form->isValid()) {
      $galleryItemsIdArray  = array_filter(explode(',', $project->getGalleryItemsId()));
      
      $intialItemsIdProject = array_filter(explode(',', $this->getGalleryItemId($project))); 
      
      $em = $this->getDoctrine()->getManager();

      $query = $em->createQuery('SELECT u FROM BackendAdminBundle:GalleryItemOrder u WHERE u.galleryItem = :giId AND u.project = :projectId');
      

      // Add new images
      foreach ($galleryItemsIdArray as $index=>$id) {
        $galleryItem      = $em->find('Backend\AdminBundle\Entity\GalleryItem', $id);

        $query->setParameters(array(
            'giId' => $id,
            'projectId' => $project->getId()
        ));

        $galleryItemOrder = $query->getOneOrNullResult();

        // This element exists we only change his order  
        if ($galleryItemOrder) {
          $galleryItemOrder->setOrderInProject($index);
        } else {
          $galleryItemOrder = new GalleryItemOrder();

          $galleryItemOrder->setGalleryItem($galleryItem);
          $galleryItemOrder->setProject($project);
          $galleryItemOrder->setOrderInProject($index);
        }

        $em->persist($galleryItemOrder);
        $em->flush();
        // We reorder elements in the projects
        // http://stackoverflow.com/questions/15616157/doctrine-2-and-many-to-many-link-table-with-an-extra-field
      }

      // Remove the items removed from the project
      foreach (array_diff($intialItemsIdProject, $galleryItemsIdArray) as $key => $galleryItemId) {
        $galleryItem = $em->find('Backend\AdminBundle\Entity\GalleryItem', $galleryItemId);
        
        $galleryItemOrder = $em->getRepository('Backend\AdminBundle\Entity\GalleryItemOrder')
                               ->findOneBy(
                                  array('project' => $project->getId(), 
                                        'galleryItem' => $galleryItemId)
                                );

        $em->remove($galleryItemOrder);
        $em->flush();
      }
 
      
      $em->persist($project);
      $em->flush();

      $request->getSession()
              ->getFlashBag()
              ->add('successMessage', 'Le projet <b>' . $project->getTitle() . '</b> a été mis à jour !');

      $dql   = 'SELECT a FROM BackendAdminBundle:Project a';
      $nbProjects = count($em->createQuery($dql)->getResult());
      $lastPage = ceil($nbProjects / $this->itemsPerPage);

      
      return $this->redirectToRoute('backend_admin_projects_display', array('page' => $lastPage), 301);
    }

    
    $uploadFolder = "uploads/gallery/";

    return $this->render('BackendAdminBundle:Projects:add-project.html.twig', 
                array(
                    'form' => $form->createView(),
                    'galleryItems' => $this->getGalleryItems(),
                    'uploadFolder' => $uploadFolder,
                ) 
            );
  }

  public function deleteProjectAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();
 
    $entity = $em->getRepository('BackendAdminBundle:Project')
                 ->find($id);
    
    if ($entity) {
      $em->remove($entity);
      $em->flush();
      $request->getSession()
            ->getFlashBag()
            ->add('successMessage', 'Le projet ' . $entity->getTitle() . ' a été correctement supprimé');
    } else {
      $request->getSession()
            ->getFlashBag()
            ->add('errorMessage', 'Il semblerait que ce projet n\'existe plus');
    }

    return $this->redirectToRoute('backend_admin_projects_display', array('page' => 1), 301);
  }
}
