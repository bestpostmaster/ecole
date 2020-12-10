<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\NoteEleve;
use App\Entity\Eleve;
use App\Entity\Note;

class NoteEleveController extends AbstractController
{
	 /**
     * Ajouter une note à un élève
     * @Route("/school/marks/add-to-student/", name="add-mark_to_student")
     */
	public function addMarkStudent(Request $request, SerializerInterface $serializer): Response
    {
        if ($content = $request->getContent())
        {
            $messageEmptyFields = 'Tous les champs(id_eleve, valeur, matiere) sont obligatoirs!';
            $params = json_decode($content, true);

            if(!isset($params['id_eleve']) || !isset($params['valeur']) || !isset($params['matiere']))
                throw new \Exception($messageEmptyFields);

            if ($params['id_eleve']=='' || $params['valeur']=='' || $params['matiere']=='')
                throw new \Exception($messageEmptyFields);

            if (!(($this -> getRepo('App:Eleve')) -> studentExists($params['id_eleve'])))
                throw new BadRequestHttpException('id_eleve inconnu : '.$params['id_eleve']);

            if (floatval($params['valeur']) < 0 || floatval($params['valeur']) >20 || !is_numeric($params['valeur']))
                throw new BadRequestHttpException('Une note doit être de type numérique entre 0 et 20');

            $newNote = new Note($params['valeur'], $params['matiere']);
            $em = $this->getDoctrine()->getManager();
            $em->persist($newNote);
            $em->flush();

            $newNoteEleve = new NoteEleve($newNote->getId(), $params['id_eleve']);
            $em->persist($newNoteEleve);
            $em->flush();


            $response = new Response($serializer->serialize($newNote, 'json'));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        else {
            throw new BadRequestHttpException('Erreur, requête vide!');
        }
    }

    /**
     * Ajouter une note à un élève
     * @Route("/school/marks/simple-add-mark/", name="simple_add_mark")
     */
    public function simpleAdd(Request $request, SerializerInterface $serializer): Response
    {
        $messageEmptyFields = 'Vous devez fournir un id_note et un id_eleve valides';
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);

            if (!(($this -> getRepo('App:Eleve')) -> studentExists($params['id_eleve'])))
                throw new BadRequestHttpException('id_eleve inconnu : '.$params['id_eleve']);

            if (!(($this -> getRepo('App:Note')) -> markExists($params['id_note'])))
                throw new BadRequestHttpException('id_note inconnu : '.$params['id_note']);

            if ((($this -> getRepo('App:NoteEleve')) -> duplicateMarkStudent($params['id_note'], $params['id_eleve'])))
                throw new BadRequestHttpException('Cette note existe déjà id_note : '.$params['id_note']);

            $newNoteEleve = new NoteEleve($params['id_note'], $params['id_eleve']);
            $em = $this->getDoctrine()->getManager();
            $em->persist($newNoteEleve);
            $em->flush();

            $response = new Response($serializer->serialize($newNoteEleve, 'json'));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        else {
            throw new BadRequestHttpException($messageEmptyFields);
        }
    }

    /**
     * Récupérer les associations Note <-> Eleve
     * @Route("/school/marks/associations", name="associations")
     */

    public function getAssociations(SerializerInterface $serializer): Response
    {
        $repository = $this -> getRepo('App:NoteEleve');
        $eleves = $repository->findAll();
        $serializedEntity = $serializer->serialize($eleves, 'json');
        if ($eleves)
        {
            $response = new Response($serializedEntity);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        else {
            throw $this->createNotFoundException('Aucune association disponible !!');
        }
    }

	 /**
     * Calculer la moyenne d'un élève
     * @Route("/school/marks/student-average", name="student_average")
     */

	public function studentAverage(Request $request): Response
    {
		if ($content = $request->getContent())
		{
			$params = json_decode($content, true);
			if(!isset($params['id']) || $params['id'] == '')
				throw new BadRequestHttpException("Erreur, vous devez fournir l'identifiant d'un elève pour calculer sa moyenne");
			
			if (!$oldEleve = (($this -> getRepo('App:Eleve')) -> studentExists($params['id'])))
				throw new BadRequestHttpException("Cet identifiant d'élève n'existe pas : ".$params['id']);
			
			$id_eleve = $params['id'];

			 if (!$notes = (($this -> getRepo('App:NoteEleve')) -> getMarks($id_eleve)))
				throw new BadRequestHttpException("Cet élève n'a pas de notes ");

            $noteCount = (($this -> getRepo('App:Note')) -> notesCount($id_eleve));
            $avg = (($this -> getRepo('App:NoteEleve')) -> getStudentAvg($id_eleve));

            throw new BadRequestHttpException('DEBUG : '.$avg);
		}
		else
		{
			throw new BadRequestHttpException('Erreur, requête vide!');
		}
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
