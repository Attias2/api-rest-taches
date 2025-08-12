<?php

namespace App\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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

    //Route pour la page principale
    #[Route('/task', name: 'app_task')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        //création du formulaire d'ajout
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        //teste si le formulaire est soumis
        if ($form->isSubmitted() && $form->isValid()) {
            
            $task = $form->getData();
            $em->persist($task);
            $em->flush();

            return $this->redirectToRoute('app_task');
        }

        //recupératuion des tâches
        $tasks = $em->getRepository(Task::class)->findAll();

        return $this->render('task/index.html.twig', [
            'form' => $form->createView(),
            'tasks' => $tasks,
        ]);
    }

    /**
     * Modification de la tâche
     */
    #[Route('/update/task/{id}', name: 'update_task')]//Récupération de l'id dans la route
    public function updateTask(Request $request, EntityManagerInterface $em, int $id = null): Response
    {
        //récupération de la tâche
        $task = $em->getRepository(Task::class)->find($id);
        //teste si la tâche existe, si non on retourne sur la route
        if($task === null){
            $this->addFlash('error', 'Aucune tâche trouvée !');
            return $this->redirectToRoute('app_task');
        }
        
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $task = $form->getData();
            $em->persist($task);
            $em->flush();

            return $this->redirectToRoute('update_task', ['id' => $task->getId()]);
        }

        return $this->render('task/updateTask.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);

    }
}
