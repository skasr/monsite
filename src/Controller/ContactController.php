<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $email = (new Email())
                ->from($data['email'])
                ->to('contact@monsite.fr')
                ->subject($data['sujet'])
                ->text(sprintf(
                    "Message de : %s (%s)\n\n%s",
                    $data['nom'],
                    $data['email'],
                    $data['message']
                ));

            try {
                $mailer->send($email);
                $this->addFlash('success', 'Votre message a bien été envoyé !');
                return $this->redirectToRoute('app_contact');
            } catch (\Exception $e) {
                $this->addFlash('success', 'Votre message a été reçu ! (Mail non configuré pour la démo)');
                return $this->redirectToRoute('app_contact');
            }
        }

        return $this->render('contact/index.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }
}