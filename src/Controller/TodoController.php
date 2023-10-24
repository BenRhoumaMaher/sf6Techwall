<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TodoController extends AbstractController
{
    #[Route('/todo', name: 'todo')]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        if(!$session->has('todos')) {
            $todos = [
                'achat' => 'acheter clé usb',
                'cours' => 'Finaliser mon cours',
                'correction' => 'corriger mes examens'
            ];
            $session->set('todos', $todos);
            $this->addFlash('info', "la liste des todos viens d'être initialisé");
        }


        return $this->render('todo/index.html.twig');
    }

    #[Route('/todo/add/{name}/{content}', name:'todo.add')]
    public function addTodo(Request $request, $name, $content): RedirectResponse
    {
        $session = $request->getSession();
        // vérifier si j'ai mon tableau de todo dans la session
        if($session->has('todos')) {
            // si oui vérifier si on a déja un todo avec le même nom
            $todos = $session->get('todos');
            if(isset($todos[$name])) {
                // si oui afficher erreur
                $this->addFlash('error', "le todo d'id $name existe deja dans la liste");
            } else {
                // si non l'ajouter et on affiche un message de succés
                $todos[$name] = $content;
                $this->addFlash('success', "le todo d'i $name a été ajouté avec succes");
                $session->set('todos', $todos);
            }
        } else {
            // si non afficher une erreur et on va rediriger vers le controller index
            $this->addFlash('error', "la liste des todos n'est pas encore initialisée");
        }
        return $this->redirectToRoute('todo');
    }

    #[Route('/todo/update/{name}/{content}', name:'todo.update')]
    public function updateTodo(Request $request, $name, $content): RedirectResponse
    {
        $session = $request->getSession();
        // vérifier si j'ai mon tableau de todo dans la session
        if($session->has('todos')) {
            // si oui vérifier si on a déja un todo avec le même nom
            $todos = $session->get('todos');
            if(!isset($todos[$name])) {
                // si oui afficher erreur
                $this->addFlash('error', "le todo d'id $name n'existe pas dans la liste");
            } else {
                // si non l'ajouter et on affiche un message de succés
                $todos[$name] = $content;
                $this->addFlash('success', "le todo d'i $name a été modifié avec succes");
                $session->set('todos', $todos);
            }
        } else {
            // si non afficher une erreur et on va rediriger vers le controller index
            $this->addFlash('error', "la liste des todos n'est pas encore initialisée");
        }
        return $this->redirectToRoute('todo');
    }

    #[Route('/todo/delete/{name}', name:'todo.delete')]
    public function deleteTodo(Request $request, $name): RedirectResponse
    {
        $session = $request->getSession();
        // vérifier si j'ai mon tableau de todo dans la session
        if($session->has('todos')) {
            // si oui vérifier si on a déja un todo avec le même nom
            $todos = $session->get('todos');
            if(!isset($todos[$name])) {
                // si oui afficher erreur
                $this->addFlash('error', "le todo d'id $name n'existe pas dans la liste");
            } else {
                // si non l'ajouter et on affiche un message de succés
                unset($todos[$name]);
                $session->set('todos', $todos);
                $this->addFlash('success', "le todo d'i $name a été supprimé avec succes");
            }
        } else {
            // si non afficher une erreur et on va rediriger vers le controller index
            $this->addFlash('error', "la liste des todos n'est pas encore initialisée");
        }
        return $this->redirectToRoute('todo');
    }

    #[Route('/todo/reset', name:'todo.reset')]
    public function resetTodo(Request $request): RedirectResponse
    {
        $session = $request->getSession();
        $session->remove('todos');
        return $this->redirectToRoute('todo');
    }
}