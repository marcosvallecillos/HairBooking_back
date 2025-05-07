<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;
final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]

    // En tu controller:
    public function index(Request $request, MailerInterface $mailer, LoggerInterface $logger): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = (new Email())
                ->from('marcosvallecillosu@gmail.com')
                ->to('marcosvallecillosu@gmail.com')
                ->subject('Contact')
                ->text('Sending emails is fun again!')
                ->html('<p>'.$form->getData()['name'].'</p>'.
                       '<p>'.$form->getData()['email'].'</p>'.
                       '<p>'.$form->getData()['text'].'</p>'
                );
            $mailer->send($email);
            $logger->info('Email enviado correctamente.');
            $this->addFlash('success', 'Your message has been sent!');
            return $this->redirectToRoute('app_contact');
        }
    
        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
}