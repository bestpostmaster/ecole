<?php
namespace App\Controller;

use App\Helpers\Helper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Entity\Eleve;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class EleveController extends AbstractController
{
    /**
     * Récupérer les informations d'un élève à l'aide de son identifiant
     * @Route("/school/students/get-one/{id}", name="get_student")
     */
    public function getStudent(Request $request, SerializerInterface $serializer): Response
    {
		if ($eleve = ($this -> getRepo('App:Eleve')) ->studentExists($request->get("id")))
		{
            $response = new Response($serializer->serialize($eleve, 'json'));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
		} 
		else {
			throw $this->createNotFoundException('Elève inconnu !!');
		}
    }
	
	/**
     * Récupérer la liste de tous les élèves
     * @Route("/school/students/get-all/", name="get_students")
     */
    public function getStudents(Request $request, SerializerInterface $serializer): Response
    {
		$eleves = ($this -> getRepo('App:Eleve'))->findAll();
		if ($eleves)
		{	
			$response = new Response($serializer->serialize($eleves, 'json'));
			$response->headers->set('Content-Type', 'application/json');
			return $response;
		} 
		else {
			throw $this->createNotFoundException('Aucun élève dans la base de données !!');
		}
    }

    /**
     * Ajouter un élève
     * @Route("/school/students/add/", name="add_student")
     */
	public function addStudent(Request $request, SerializerInterface $serializer, Helper $helper): Response
    {
		if ($content = $request->getContent())
		{
			$params = json_decode($content, true);
            $newEleve = new Eleve(strtoupper($params['nom']), $params['prenom'], $params['dateNaiss']);

            try {
                $newEleve -> validateEleve();
            }
            catch (Exception $e) {
                throw new BadRequestHttpException($e->getMessage());
            }
				
			if (($this -> getRepo('App:Eleve'))-> duplicateStudent($params['nom'], $params['prenom'], $params['dateNaiss']))
				throw new BadRequestHttpException('Cet élève existe déjà');

			$em = $this->getDoctrine()->getManager();
			$em->persist($newEleve);
			$em->flush();

            $response = new Response($serializer->serialize($newEleve, 'json'));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
			
		} 
		else {
			throw new BadRequestHttpException('Erreur, requête vide!');
		}
    }

    /**
     * Modifier un élève
     * @Route("/school/students/edit/", name="edit_student")
     */
	 
	public function editStudent(Request $request, SerializerInterface $serializer): Response
    {
		if ($content = $request->getContent())
		{
			$params = json_decode($content, true);
			if(!isset($params['id']) || !isset($params['nom']) || !isset($params['prenom']) || !isset($params['dateNaiss']))
				throw new BadRequestHttpException('Tous les champs id, nom, prenom, dateNaiss sont obligatoirs!');
			
			if (!$this -> validateDate($params['dateNaiss'], 'd/m/Y'))
				throw new BadRequestHttpException('Format de la date invalide. Utilisez le format : JJ/MM/AAAA');
				
			if (($this -> getRepo('App:Eleve')) -> duplicateStudent ($params['nom'], $params['prenom'], $params['dateNaiss']))
				throw new BadRequestHttpException('Cet élève existe déjà : '.$params['nom'].' '.$params['prenom'].' né le : '.$params['dateNaiss']);

			if ($oldEleve = ($this -> getRepo('App:Eleve')) ->studentExists($params['id']))
			{
				$em = $this->getDoctrine()->getManager();
				$oldEleve -> setNom($params['nom']);
				$oldEleve -> setPrenom($params['prenom']);
				$oldEleve -> setdateNaiss($params['dateNaiss']);
				$em->persist($oldEleve);
				$em->flush();

                $response = new Response($serializer->serialize($oldEleve, 'json'));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
			}
			else
				throw $this->createNotFoundException('Elève inconnu !!');
		}
		else {
			throw new BadRequestHttpException('Erreur, requête vide!');
		}
    }
	
	 /**
     * Supprimer un élève
     * @Route("/school/students/delete/", name="delete_student")
     */
	public function deleteStudent(Request $request, SerializerInterface $serializer): Response
    {
		if ($content = $request->getContent())
		{
			$params = json_decode($content, true);
			if(!isset($params['id']))
				throw new \Exception('Voud devez fournir un ID');
				
			if ($oldEleve = ($this -> getRepo('App:Eleve')) -> studentExists($params['id']))
			{
				$id = $oldEleve -> getId();
				$em = $this->getDoctrine()->getManager();
				$em->remove($oldEleve);
				$em->flush();
				
				//Supprimer les notes de l'élève
                 $this -> getRepo('App:NoteEleve') -> removeStudentMarks ($id);
                $oldEleve -> setId($id);

                $response = new Response($serializer->serialize($oldEleve, 'json'));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
			}
			else
				throw $this->createNotFoundException('Elève inconnu !!');
		} 
		else
			throw new \Exception('Erreur, requête vide!');

    }

	 /**
     * Récupérer le repository de Doctrine
     */
	
	private function getRepo ($repoName)
	{
		$repository = $this
		->getDoctrine()
		->getManager()
		->getRepository($repoName);
		
		return $repository;
	}

}
