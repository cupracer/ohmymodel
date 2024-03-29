<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\ReferrerService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'start')]
    public function homepageNoLocale(SessionInterface $session): Response
    {
        if($session->get('_locale')) {
            return $this->redirectToRoute('homepage', ['_locale' => $session->get('_locale')]);
        }else {
            return $this->redirectToRoute('homepage', ['_locale' => 'en']);
        }
    }

    #[Route('/{_locale<%app.supported_locales%>}/', name: 'homepage')]
    public function homepage(): Response
    {
        return $this->render('homepage.html.twig');
    }

    #[Route('/user/locale/{_locale<%app.supported_locales%>}/', name: 'app_user_locale')]
    public function index(string $_locale, Request $request, ReferrerService $referrerService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if($user) {
            $user->getUserProfile()->setLocale($request->getSession()->get('_locale'));
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }

        $referrerUrl = $referrerService->getReferrerUrl($request);

        if($referrerUrl) {
            //TODO: Do we need to use URL query parameters as well?
            return $this->redirectToRoute($referrerUrl, ['_locale' => $_locale]);
        }

        return $this->redirectToRoute('homepage', ['_locale' => $_locale]);
    }

    #[Route('/api/ping', name: 'api_ping')]
    public function apiPing(): Response
    {
        return new JsonResponse([
            'success' => true,
            'message' => "pong"
        ]);
    }

    #[Route('/api/authcheck', name: 'api_authcheck')]
    #[IsGranted("ROLE_API")]
    public function apiAuthCheck(TranslatorInterface $translator): Response
    {
        return new JsonResponse([
            'success' => true,
            'message' => $translator->trans('Authenticated.')
        ]);
    }
}
