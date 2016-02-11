<?php

namespace Backend\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Backend\AdminBundle\Entity\Project;

class APIController extends Controller
{
    private function getGalleryItem($project)
    {
      $projectsJSON = array();
      $projectImages = [];

      foreach ($project->getGalleryItemsOrder() as $galleryItemOrder) {
        $path = $galleryItemOrder->getGalleryItem()->getWebPath();
        $projectImages[] = $this->get('liip_imagine.cache.manager')->getBrowserPath($path, 'select_image_front');
      }

      return $projectImages;
    }

    private function getThumbProject($project, $filter = 'thumb_menu_front')
    {
      $galleryItemOrder = $project->getGalleryItemsOrder()[0];
      $path = $galleryItemOrder->getGalleryItem()->getWebPath(); 

      return $this->get('liip_imagine.cache.manager')->getBrowserPath($path, $filter);
    }

    public function getProjectsAction($category = null)
    {
      $em = $this->getDoctrine()->getManager();

      $query = $em->createQueryBuilder('p')
                  ->select('p')
                  ->from('BackendAdminBundle:Project', 'p')
                  ->andWhere('p.isOnline = 1')
                  ->leftJoin('p.category', 'c');

      if ($category !== null && $category !== "all") {

        $query->andWhere('c.name = :categoryName')
              ->setParameters(array('categoryName' => $category));
      }
      
      $projects = $query->getQuery()->getResult();

      /*{
        "id": 12,
        "name": null,
        "thumb": "d_chantier_chartres3-thumb.jpg",
        "description": "En chantier !",
        "category": "perso",
        "online": true,
        "images": "p_chantier_chartres1.jpg,p_chantier_chartres2.jpg,p_chantier_chartres3.jpg,p_chantier_chartres4.jpg",
        "createdAt": "17/09/2013 12:00:06",
        "realisedAt": "17/09/2013"
      }*/
      
      $projectsJSON = array();
      foreach ($projects as $key => $project) {
        $projectsJSON[] = array(
                              'id'          => $project->getId(), 
                              'name'        => $project->getTitle(),
                              'thumb'       => $this->getThumbProject($project),
                              'description' => $project->getDescription(), 
                              'category'    => $project->getCategory()->getSlugName(),
                              'images'      => implode(',', $this->getGalleryItem($project)),
                              'online'      => true,
                              'createdAt'   => $project->getCreatedAt()->format('Y-m-d H:i:s'),
                              'realisedAt'  => '17/09/2013'
                             );
      }

      return new JsonResponse($projectsJSON);
    }

    public function getRandomProjectAction($category) {
      $em = $this->getDoctrine()->getManager();

      $categoryId = $em->getRepository('Backend\AdminBundle\Entity\Category')->findOneBy(array('slugName' => $category));
      $categoryId = $categoryId->getId();

      $projects = $em->getRepository('Backend\AdminBundle\Entity\Project')->findBy(
                 array('category' => $categoryId, 'isOnline' => true));

      shuffle($projects);

      if (count($projects) == 0) {
        return new JsonResponse(array('error' => 'there is no project for this category'));
      }

      $project = $projects[0];

      $projectJSON = array(
                      'id'          => $project->getId(), 
                      'name'        => $project->getTitle(),
                      'thumb'       => $this->getThumbProject($project),
                      'description' => $project->getDescription(), 
                      'category'    => $project->getCategory()->getSlugName(),
                      'images'      => implode(',', $this->getGalleryItem($project)),
                      'online'      => true,
                      'createdAt'   => $project->getCreatedAt()->format('Y-m-d H:i:s'),
                      'realisedAt'  => '17/09/2013'
                     );

      return new JsonResponse($projectJSON);
    }

    public function getProjectAction($id) {
      $em = $this->getDoctrine()->getManager();

      $project = $em->find('Backend\AdminBundle\Entity\Project', $id);

      // A project with this id doesn't exist
      if (null === $project) {
        return new JsonResponse(array('error' => 'a project with this id doesn\'t exist'));
      }

      if ($project->getIsOnline() === false) {
        return new JsonResponse(array('error' => 'this project is not online currently'));
      }

      $projectJSON = array(
                      'id'          => $project->getId(), 
                      'name'        => $project->getTitle(),
                      'thumb'       => $this->getThumbProject($project),
                      'description' => $project->getDescription(), 
                      'category'    => $project->getCategory()->getSlugName(),
                      'images'      => implode(',', $this->getGalleryItem($project)),
                      'online'      => true,
                      'createdAt'   => $project->getCreatedAt()->format('Y-m-d H:i:s'),
                      'realisedAt'  => '17/09/2013'
                     );

      return new JsonResponse($projectJSON);
    }


    public function getSliderAction($category = null)
    {
      $em = $this->getDoctrine()->getManager();

      $query = $em->createQueryBuilder('p')
                  ->select('p')
                  ->from('BackendAdminBundle:Project', 'p')
                  ->andWhere('p.isOnline = 1')
                  ->leftJoin('p.category', 'c');

      if ($category !== null && $category !== "all") {

        $query->andWhere('c.name = :categoryName')
              ->setParameters(array('categoryName' => $category));
      }
      
      $projects = $query->getQuery()->getResult();

      $projectsJSON = array();
      foreach ($projects as $key => $project) {
        $projectsJSON[] = array(
                              'id'       => $project->getId(),
                              'name'     => $project->getTitle(),
                              'category' => $project->getCategory()->getSlugName(),
                              'imgPath'  => $this->getThumbProject($project, $filter = 'image_slider_front'),
                             );
      }

      return new JsonResponse($projectsJSON);
    }
}
