<?php

namespace Backend\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


use Backend\AdminBundle\Entity\Project;

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
    foreach ($project->getGalleryItems() as $galleryItem) {
      $ids[] = $galleryItem->getId();
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
                  'id' => "perojectImagesId",
                  'value' => "12"
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
        // $galleryItem->setOrder($index);
        $project->addGalleryItem($galleryItem);

        $galleryItem->addProject($project);
        $em->persist($galleryItem);
      }

       // array_search('green', $array);

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
    
    if ($form->isValid()) {
      $galleryItemsIdArray = array_filter(explode(',', $project->getGalleryItemsId()));

      $intialItemsIdProject = array_filter(explode(',', $this->getGalleryItemId($project))); 
      
      $em = $this->getDoctrine()->getManager();
      
      // Add new images
      foreach ($galleryItemsIdArray as $index=>$id) {
        $galleryItem = $em->find('Backend\AdminBundle\Entity\GalleryItem', $id);
    
        if (!$project->getGalleryItems()->contains($galleryItem)) {
          $project->addGalleryItem($galleryItem);
          $galleryItem->addProject($project);

          $em->persist($galleryItem);
        }
        // We reorder elements in the projects
        // http://stackoverflow.com/questions/15616157/doctrine-2-and-many-to-many-link-table-with-an-extra-field
        // echo array_search($id, $galleryItemsIdArray);
        // $galleryItem->setOrder(array_search($id, $galleryItemsIdArray));
      }

      // Remove old images
      foreach ($intialItemsIdProject as $index=>$id) {
        $galleryItem = $em->find('Backend\AdminBundle\Entity\GalleryItem', $id);

        if (!in_array($id, $galleryItemsIdArray) && $project->getGalleryItems()->contains($galleryItem)) { 

          $project->removeGalleryItem($galleryItem);
          $galleryItem->removeProject($project);

          $em->persist($galleryItem);
        }

        
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
            ->add('successMessage', 'Le projet ' . $entity->getTitle() . ' a été correctement supprimée');
    } else {
      $request->getSession()
            ->getFlashBag()
            ->add('errorMessage', 'Il semblerait que ce projet n\'existe plus');
    }

    return $this->redirectToRoute('backend_admin_projects_display', array('page' => 1), 301);
  }
}
