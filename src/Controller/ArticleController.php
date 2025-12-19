<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/articles')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_article_index')]
    public function index(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll();

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/nouveau', name: 'app_article_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setAuteur($this->getUser());
            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article créé avec succès !');

            return $this->redirectToRoute('app_article_index');
        }

        return $this->render('article/new.html.twig', [
            'articleForm' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', requirements: ['id' => '\d+'])]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_article_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($article->getAuteur() !== $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez modifier que vos propres articles.');
            return $this->redirectToRoute('app_article_index');
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Article modifié avec succès !');

            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'articleForm' => $form->createView(),
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_article_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($article->getAuteur() !== $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez supprimer que vos propres articles.');
            return $this->redirectToRoute('app_article_index');
        }

        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article supprimé avec succès !');
        }

        return $this->redirectToRoute('app_article_index');
    }
}