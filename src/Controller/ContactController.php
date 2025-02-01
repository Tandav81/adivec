<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $contactFormData = $form->getData();
            $message = (new TemplatedEmail())
                ->from('mailpourenvoi@lafamilledavid.fr')
                ->to('contact@adivec.fr')
                ->subject('Demande client via le site adivec')
                ->htmlTemplate('emails/demande_client.html.twig')
                // pass variables (name => value) to the template
                ->context([
                    'formdata' => $contactFormData,
                ]);
            $mailer->send($message);
            $this->addFlash('success', 'Vore message a été envoyé');
            return $this->redirectToRoute('app_home');
        }
        return $this->render('contact/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
