<?php
namespace App\Controller;

use App\Helpers\Helper;
use App\Repository\EleveRepository;
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
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Helper $helper
     * @return Response
     */
    public function getStudent(Request $request, SerializerInterface $serializer, Helper $helper): Response
    {
		if ($eleve = ($helper -> getRepo('App:Eleve')) ->studentExists($request->get("id")))
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
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Helper $helper
     * @return Response
     */
    public function getStudents(Request $request, SerializerInterface $serializer, Helper $helper): Response
    {
		$eleves = ($helper -> getRepo('App:Eleve'))->findAll();
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
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Helper $helper
     * @return Response
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
				
			if (($helper -> getRepo('App:Eleve'))-> duplicateStudent($params['nom'], $params['prenom'], $params['dateNaiss']))
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
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Helper $helper
     * @return Response
     */
	 
	public function editStudent(Request $request, SerializerInterface $serializer, Helper $helper): Response
    {
		if ($content = $request->getContent())
		{
			$params = json_decode($content, true);

			$newEleve = new Eleve($params['nom'], $params['prenom'], $params['dateNaiss']);
            try {
                $newEleve -> validateEleve();
            }
            catch (Exception $e) {
                throw new BadRequestHttpException($e->getMessage());
            }

			if ($oldEleve = ($helper -> getRepo('App:Eleve')) ->studentExists($params['id']))
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
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Helper $helper
     * @return Response
     * @throws \Exception
     */
	public function deleteStudent(Request $request, SerializerInterface $serializer, Helper $helper): Response
    {
		if ($content = $request->getContent())
		{
			$params = json_decode($content, true);
			if(!isset($params['id']))
				throw new \Exception('Voud devez fournir un ID');
				
			if ($oldEleve = ($helper -> getRepo('App:Eleve')) -> studentExists($params['id']))
			{
				$id = $oldEleve -> getId();
				$em = $this->getDoctrine()->getManager();
				$em->remove($oldEleve);
				$em->flush();
				
				//Supprimer les notes de l'élève
                $helper -> getRepo('App:NoteEleve') -> removeStudentMarks ($id);
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
}
