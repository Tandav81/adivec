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
     * Pré-remplissage quand on arrive depuis le formulaire « Demander un devis » d'une fiche produit :
     *   /contact?produit={slug}&quantite=500&unite=kg   (unite : kg, t, ou p{id} = un conditionnement du produit)
     * Toutes les valeurs sont contrôlées : produit existant, quantité numérique, unité de la liste.
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

        $data = [
            'produit' => $product->getNom(),
            'message' => sprintf("Bonjour,\n\nJe souhaiterais recevoir un devis pour le produit « %s ».\n\n", $product->getNom()),
        ];

        $quantity = str_replace([' ', ','], ['', '.'], (string) $request->query->get('quantite', ''));
        if (is_numeric($quantity) && (float) $quantity > 0 && (float) $quantity < 1e7) {
            $amount = rtrim(rtrim(number_format((float) $quantity, 2, ',', ' '), '0'), ',');
            $unit = (string) $request->query->get('unite', 'kg');
            $units = ['kg' => 'kg', 't' => 'tonne(s)'];
            foreach ($product->getPackagings() as $packaging) {
                $units['p' . $packaging->getId()] = '× ' . $packaging;
            }
            $data['quantite'] = $amount . ' ' . ($units[$unit] ?? 'kg');
        }

        return $data;
    }
}
