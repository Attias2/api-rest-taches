<?php

namespace App\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\TaskType;


class TaskController extends AbstractController
{
    //Redirection automatique vers la route amenant à la page principale
    #[Route('/', name: 'home')]
    public function home(): RedirectResponse
    {
        return $this->redirectToRoute('app_task');
    }
    
    #[Route('/task', name: 'app_task')]
    public function index(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        // création du formulaire d'ajout
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        //Teste si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            $em->persist($task);
            $em->flush();

            //Stocke l'id de la tâche créée en session
            $session->set('lastCreatedTaskId', $task->getId());

            //Redirection
            return $this->redirectToRoute('app_task');
        }

        // Récupère la tâche créée depuis la session
        $taskAEntity = null;
        $lastCreatedTaskId = $session->get('lastCreatedTaskId');
        if ($lastCreatedTaskId) {
            $taskAEntity = $em->getRepository(Task::class)->find($lastCreatedTaskId);
            // Supprime la clé en session pour que ça n'apparaisse plus au prochain chargement
            $session->remove('lastCreatedTaskId');
        }

        $tasks = $em->getRepository(Task::class)->findAll();

        return $this->render('task/index.html.twig', [
            'form' => $form->createView(),
            'tasks' => $tasks,
            'taskA' => $taskAEntity,
        ]);
    }

    /** 
     *modification de tâche 
     * 
    */
    #[Route('/update/task/{id}', name: 'update_task')]
    public function updateTask(Request $request, EntityManagerInterface $em, SessionInterface $session, int $id = 0): Response
    {
        //récupération de la tâche
        $task = $em->getRepository(Task::class)->find($id);

        //Teste si la tâche existe
        if ($task === null) {
            $this->addFlash('error', 'Aucune tâche trouvée !');
            return $this->redirectToRoute('app_task');
        }

        // Stockage dees anciennes valeurs dans un tableau avant modification
        $oldTaskData = [
            'title' => $task->getTitle(),
            'status' => $task->getStatus(),
            'description' => $task->getDescription()
        ];

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($task);
            $em->flush();
            
            //Stocke un booleen qui sert à tester si une modification est faite
            $session->set('isModified', true);

            // Stockage aussi des anciennes valeurs en session pour les récupérer après la redirection
            $session->set('oldTaskData', $oldTaskData);

            return $this->redirectToRoute('update_task', ['id' => $task->getId()]);
        }

        // Récupère du booléen qui teste si il y a eu une modification
        $isModified = $session->get('isModified', false);
        if ($isModified) {
            $session->remove('isModified');
        }

        // Récupératrion des anciennes valeurs en session, puis supprettion de la clé
        $oldTaskData = $session->get('oldTaskData', null);
        if ($oldTaskData) {
            $session->remove('oldTaskData');
        }

        return $this->render('task/updateTask.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
            'oldTaskData' => $oldTaskData,
            'isModified' => $isModified,
        ]);
    }




    /**
     * Supression de la tâche
     */
    #[Route('/delete/task/{id}', name: 'delete_task')]//Récupération de l'id dans la route
    public function deleteTask(Request $request, EntityManagerInterface $em, int $id = 0): Response
    {
        //récupération de la tâche
        $task = $em->getRepository(Task::class)->find($id);

        //teste si la tâche existe, si non on retourne sur la route
        if($task === null){
            $this->addFlash('error', 'Aucune tâche trouvée !');
            return $this->redirectToRoute('app_task');
        }
        $title = $task->getTitle();
        $em->remove($task);
        $em->flush();
        $this->addFlash('success', "Tâche $title supprimée !");
        return $this->redirectToRoute('app_task');

    }

    /**
     * Change l'état de la tâche
     */
    #[Route('/update/status', name: 'update_status', methods: ['POST'])]//Récupération de l'id dans la route et le met à 0 par défaut
    public function updateStatus(Request $request, EntityManagerInterface $em): JsonResponse|Response
    {
        //teste si 
        if($request->isXmlHttpRequest()) {

            //récupération des données
            $data = json_decode($request->getContent(), true);
            //récupération de la tâche
            $task = $em->getRepository(Task::class)->find($data['id']);

            $status = $data['status'];

            //teste si la tâche existe, si on renvoit un message d'erreur
            if($task === null){
                return new JsonResponse(['message' => 'Aucune tâche trouvée !'], Response::HTTP_BAD_REQUEST);
            }

            //teste si le status est correct
            if(!in_array($status, ['hors programme', 'en cours', 'terminée'])){
                return new JsonResponse(['message' => 'Status invalide !'], Response::HTTP_BAD_REQUEST);
            }

            try {
                $title = $task->getTitle();
                $task->setStatus($status);
                $em->flush();
            } catch  (Exception $e) {
                return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
            }
            
            return new JsonResponse(['message' => "Tâche ".$title." ".$status]);
        }
        else{
            return new JsonResponse(['message' => 'Cet appel doit être effectué via AJAX.'], Response::HTTP_BAD_REQUEST);
        }
    }
}
