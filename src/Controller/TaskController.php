<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\StatusEnum;
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
    public function __construct(private EntityManagerInterface $entityManager) {}

    /**
     * Redirection automatique vers la route amenant à la page principale
     * 
     * 
     * @return RedirectResponse
     */
    #[Route('/', name: 'home')]
    public function home(): RedirectResponse
    {
        return $this->redirectToRoute('app_task');
    }
    

    /**
     *
     * 
     * @return Response
    */
    #[Route('/task', name: 'app_task')]
    public function index(): Response
    {
        return $this->render('task/index.html.twig');
    }

    /**
     * Lister les tâches
     * 
     * @param Request $request
     * 
     * @return JsonResponse
     * 
    */
    #[Route('/lst/tasks', name: 'lst_tasks', methods: ['GET'])]
    public function lstTasks(Request $request): JsonResponse
    {
        // Vérifie si la requête est AJAX ou JSON
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['message' => 'Cet appel doit être effectué via AJAX.'], JsonResponse::HTTP_BAD_REQUEST);
        }


        //récupération des Tâches
        $tasks =  $this->entityManager->getRepository(Task::class)->findAll();


        //stockages de donnée dans un tableu simple pour economiser l'espace mémoir
        //il sera traité aver une boucle for et on s'y repéra de par les numero des key
        $dataTasks = [];
        foreach($tasks as $task){
            $dataTasks[] = $task->getId();
            $dataTasks[] = $task->getTitle();
            $dataTasks[] = $task->getStatus();
            $dataTasks[] = $task->getDescription();
            $dataTasks[] = $task->getCreatedAt()->format('Y-m-d H:i:s');// les date sont converties en string pour ne avoir à traiter des objets
            $dataTasks[] = $task->getUpdatedAt()->format('Y-m-d H:i:s');
        }

        // Réponse JSON
        return new JsonResponse([
            'message' => NULL,
            'dataTasks' => $dataTasks,
        ]);
    }

    /**
     * Ajouter une tâche
     * 
     * @param Request $request
     * 
     * @return JsonResponse
    */
    #[Route('/add/task', name: 'add_task', methods: ['POST'])]
    public function addTask(Request $request): JsonResponse
    {
        // Vérifie si la requête est AJAX ou JSON
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['message' => 'Cet appel doit être effectué via AJAX.'], JsonResponse::HTTP_BAD_REQUEST);
        }


        //récupération des données transmises
        $data = json_decode($request->getContent(), true);

        // teste de la présence des champs attendu
        if (!isset($data['title']) || !isset($data['description']) || !isset($data['status'])) {
            return new JsonResponse(['message' => 'Les champs title, description et status sont requis !'], JsonResponse::HTTP_BAD_REQUEST);
        }

        //message d'erreur à afficher en cas d'erreur
        $message = '';
        //booleen qui teste si un champs a mal été saisi
        $error = false;
        $status = $data['status'];

        //teste si le status est correct
        if(!in_array($status, ['en retard', 'en cours', 'terminée'])){
            $message .= 'Status invalide ! ';
            $error = true;
        }

        $title = $data['title'];

        //teste si le titre est saisi
        if($title === ""){
            $message .= 'Titre non saisi ';
            $error = true;
        }


        $description = $data['description'];

        //teste si la description est saisie
        if($description === ""){
            $message .= 'description non saisie';
            $error = true;
        }

        //renvoi un message en cas de mauvaise saisi
        if($error){
            return new JsonResponse(['message' => $message], Response::HTTP_BAD_REQUEST);
        }
        
        // création de la tâche
        $task = new Task();
        $task->setTitle($title);
        $task->setDescription($description);
        
        // conversion de string en enum
        $task->setStatus(StatusEnum::from($status));
        $task->setCreatedAtValue();

        try {
            $this->entityManager->persist($task);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Erreur lors de la création de la tâche : '.$e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }


        // Réponse JSON
        return new JsonResponse([
            'message' => "Tâche '{$task->getTitle()}' créée avec succès !",
            'taskCreated' => [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
                'createdAt' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $task->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /** 
     * modification de tâche
     * 
     * @param Request Request
     * @param SessionInterface $session
     * @param id $id
     * 
     * @return Response|RedirectResponse
     * 
     */
    #[Route('/update/task/{id}', name: 'update_task', methods: ['GET', 'POST'])]
    public function updateTask(Request $request, SessionInterface $session, int $id): Response|RedirectResponse 
    {
        //récupération de la tâche
        $task = $this->entityManager->getRepository(Task::class)->find($id);

        //Teste si la tâche existe
        if ($task === null) {
            $this->addFlash('error', 'Aucune tâche trouvée !');
            return $this->redirectToRoute('app_task');
        }

        // Stockage dees anciennes valeurs dans un tableau avant modification
        $oldTaskData = [
            'title' => $task->getTitle(),
            'status' => $task->getStatus()?->value,
            'description' => $task->getDescription()
        ];

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($task);
            $this->entityManager->flush();
            
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
     * Supression d'une tâche
     * 
     * @param Request $request
     * @param int $id
     * 
     * @return JsonResponse
     */
    #[Route('/delete/{id}/task/', name: 'delete_task', methods: 'DELETE')]
    public function deleteTask(Request $request, int $id = 0): JsonResponse
    {
        // Vérifie si la requête est AJAX ou JSON
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['message' => 'Cet appel doit être effectué via AJAX.'], JsonResponse::HTTP_BAD_REQUEST);
        }
            
        //récupération de la tâche
        $task = $this->entityManager->getRepository(Task::class)->find($id);
        
        //teste si la tâche n'existe pas, si on renvoit un message d'erreur
        if($task === null){
            return new JsonResponse(['message' => 'Aucune tâche trouvée !'], Response::HTTP_NOT_FOUND);
        }

        //récupération du titre pour le message de confirmation
        $title = $task->getTitle();

        $this->entityManager->remove($task);
        $this->entityManager->flush();

        return new JsonResponse([
            'delete' => !($task === null),
            'message' => "Tâche $title supprimée"
        ]);

    }

    /**
    * 
    * @param Request $request
    * @param  int $id
    *
    * @return JsonResponse
    *
    *
    * Change l'état de la tâche
    */
    #[Route('/update/{id}/status', name: 'update_status', methods: ['PATCH'])]
    public function updateStatus(Request $request, int $id = 0): JsonResponse
    {
        // Vérifie si la requête est AJAX ou JSON
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['message' => 'Cet appel doit être effectué via AJAX.'], JsonResponse::HTTP_BAD_REQUEST);
        }
            
        //récupération de la tâche
        $task = $this->entityManager->getRepository(Task::class)->find($id);
        
        //teste si la tâche n'existe pas, si on renvoit un message d'erreur
        if($task === null){
            return new JsonResponse(['message' => 'Aucune tâche trouvée !'], Response::HTTP_NOT_FOUND);
        }

        //récupération des données
        $data = json_decode($request->getContent(), true);

        $status = $data['status'];

        // Vérification de la présence du status
        if (!isset($data['status'])) {
            return new JsonResponse(['message' => 'Le champ "status" est requis !'], JsonResponse::HTTP_BAD_REQUEST);
        }

        //teste si le status est correct
        if(!in_array($status, ['en retard', 'en cours', 'terminée'])){
            return new JsonResponse(['message' => 'Status invalide !'], Response::HTTP_BAD_REQUEST);
        }
        // conversion de string en enum
        $task->setStatus(StatusEnum::from($status));
        $this->entityManager->flush();

        $title = $task->getTitle();
        return new JsonResponse([
            'taskStatusUdated' => [
                'id' => $task->getId(),
                'title' => $title,
                'status' => $status,
                'updatedAt' => $task->getUpdatedAt()->format('Y-m-d H:i:s'),
            ],
            'message' => "Tâche $title $status"
        ]);

        
    }
}
