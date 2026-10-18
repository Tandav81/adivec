<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContactType::class, $this->prefill($request, $entityManager));
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $contactFormData = $form->getData();
            $message = (new TemplatedEmail())
                ->from('contactsite@adivec.fr')
                ->to('contact@adivec.fr')
                ->subject('Demande client via le site adivec')
                ->htmlTemplate('emails/demande_client.html.twig')
                // pass variables (name => value) to the template
                ->context([
                    'formdata' => $contactFormData,
                ]);
            $mailer->send($message);
            $this->addFlash('success', 'Votre message a été envoyé');
            return $this->redirectToRoute('app_home');
        }
        return $this->render('contact/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Message pré-rempli quand on arrive depuis une fiche produit :
     *   /contact?produit={slug}               → demande de devis
     *   /contact?produit={slug}&demande=fiche → fiche technique / échantillon
     */
    private function prefill(Request $request, EntityManagerInterface $entityManager): ?array
    {
        $slug = $request->query->get('produit');
        if (!$request->isMethod('GET') || !\is_string($slug) || '' === $slug) {
            return null;
        }
        $product = $entityManager->getRepository(Product::class)->findOneBy(['slug' => $slug]);
        if (!$product) {
            return null;
        }
        $objet = 'fiche' === $request->query->get('demande')
            ? 'la fiche technique et un échantillon'
            : 'un devis';

        return ['message' => sprintf("Bonjour,\n\nJe souhaiterais recevoir %s pour le produit « %s ».\n\n", $objet, $product->getNom())];
    }
}
