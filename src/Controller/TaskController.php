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

    //route construisant la page principale
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

    #[Route('/update/status/{id}', name: '/update_Status', methods: ['POST'])]
    public function viewTask(Request $request, EntityManagerInterface $em): JsonResponse|Response
    {
        if($request->isXmlHttpRequest()) {
            $data = json_decode($request->getContent(), true);
            $message = 'Tâche non enregistée';
            if (isset($data['id'])) {
                $task = $em->getRepository(Task::class)->findOneBy(
                    ['id' => $data['id']],
                );
                
                return new JsonResponse([ 'task' => $task]);
            }
            else{
                return new JsonResponse(['message' => $message]);
            }
            
        }
        return new JsonResponse(['error' => 'Cet appel doit être effectué via AJAX.'], Response::HTTP_BAD_REQUEST);
    }
}
