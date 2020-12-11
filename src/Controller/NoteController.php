<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Note;

class NoteController extends AbstractController
{
	
	/**
     * Lister toutes les notes
     * @Route("/school/marks/get-all/", name="get_all_marks")
     */
    public function studentsMarks(SerializerInterface $serializer): Response
    {
		$repository = $this -> getRepo('App:Note');
		$eleves = $repository->findAll();
		$serializedEntity = $serializer->serialize($eleves, 'json');
		if ($eleves)
		{	
			$response = new Response($serializedEntity);
			$response->headers->set('Content-Type', 'application/json');
			return $response;
		} 
		else {
			throw $this->createNotFoundException('Aucune note disponible !!');
		}
    }

	/**
     * Supprimer une note
     * @Route("/school/marks/delete/", name="delete_mark")
     */
	public function deleteMark(Request $request): Response
    {
		if ($content = $request->getContent())
		{
			$params = json_decode($content, true);
			if(!isset($params['id']))
				throw new \Exception('Vous devez fournir un ID de note');
				
			if ($oldNote = ($this -> getRepo('App:Note')) -> markExists($params['id']))
			{
				$id = $oldNote -> getId();
				$em = $this->getDoctrine()->getManager();
				$em->remove($oldNote);
				$em->flush();

                //Supprimer les associations eleve <-> note
                $this -> getRepo('App:NoteEleve') -> removeAssoc ($id);
				
				$response = new JsonResponse();
				$response->setData(['id' => $id]);
				return $response;	
			}
			else
				throw $this->createNotFoundException('Note inconnue !!');
		} 
		else
			throw new \Exception('Erreur, requête vide!');

    }
	
	 /**
     * Ajouter une note
     * @Route("/school/marks/add/", name="add-mark")
     */
	public function addMark(Request $request): Response
    {
		if ($content = $request->getContent())
		{
			$messageEmptyFields = 'Tous les champs(valeur, matiere) sont obligatoirs!';
			$params = json_decode($content, true);

			if ($params['valeur']=='' || $params['matiere']=='')
				throw new \Exception($messageEmptyFields);

			if (floatval($params['valeur']) < 0 || floatval($params['valeur']) >20 || !is_numeric($params['valeur']))
				throw new BadRequestHttpException('Une note doit être de type numérique entre 0 et 20');
				
			if (($this -> getRepo('App:Note')) -> duplicateMark($params['valeur'], $params['matiere']))
				throw new BadRequestHttpException('Cette note existe déjà : '.$params['matiere'].' '.$params['valeur']);
				
			$newNote = new Note($params['valeur'], $params['matiere']);

			$em = $this->getDoctrine()->getManager();
			$em->persist($newNote);
			$em->flush();
			
			$response = new JsonResponse();
			$response->setData(['id' => $newNote->getId(), 'valeur' => $newNote->getValeur(), 'matiere' => $newNote->getMatiere()]);
			return $response;
		}
		else {
			throw new BadRequestHttpException('Erreur, requête vide!');
		}
    }

	 /**
     * Calculer la moyenne de la classe
     * @Route("/school/marks/class-average", name="class_average")
     */	
	public function classAverage(Request $request): Response
    {
        $notes = ($this -> getRepo('App:Note')) -> getAllMarks();

		if (!$notes)
			throw new BadRequestHttpException('Aucune note dans la base de données');

        if ($avg = (($this -> getRepo('App:Note')) -> getClassAvg()))
            return '{"avg":"'.$avg.'"}';
		else
            throw new BadRequestHttpException('Impossible de calculer la moyenne! ');
    }
	
	 /**
     * Récupérer le repository de Doctrine
     */
	
	private function getRepo (string $repoName)
	{
		$repository = $this
		->getDoctrine()
		->getManager()
		->getRepository($repoName);
		
		return $repository;
	}

}
