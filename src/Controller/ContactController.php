<?php

namespace App\Controller;

use App\Constant\Message;
use App\Form\ContactType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactController extends AbstractController
{
    public function __construct(
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
    ) {
    }

    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contactFormData = $form->getData();
            if (!is_array($contactFormData) || !is_string($contactFormData['email']) || !is_string($contactFormData['message']) || !is_string($this->getParameter('contact_mail_varsports'))) {
                throw new \RuntimeException(Message::GENERIC_ERROR);
            }

            $email = (new TemplatedEmail())
                ->from(new Address($contactFormData['email']))
                ->to($this->getParameter('contact_mail_varsports'))
                ->subject(Message::CONTACT_FORM)
                ->htmlTemplate('contact/email.html.twig')
                ->context([
                    'message' => $contactFormData['message'],
                    'email_from' => $contactFormData['email'],
                ])
            ;

            $this->mailer->send($email);

            $this->addFlash('success', $this->translator->trans(Message::GENERIC_SUCCESS));

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
