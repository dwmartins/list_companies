<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserRegister;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request)
    {
        $title = "Novo usuário";
        
        $user = new User();
        $form = $this->createForm(UserRegister::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $encoder = $this->container->get('security.password_encoder');
            $encoded = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($encoded);
            $user->setRoles(['ROLE_ADMIN']);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('listCompanies');
        }

        return $this->render('users/register.html.twig', array(
            'form' => $form->createView(),
            'title' => $title
        ));
    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('searchCompanies');
        }

        $authUtils = $this->get('security.authentication_utils');
        $error = $authUtils->getLastAuthenticationError();
        $lastEmail = $authUtils->getLastUsername();


        return $this->render('users/login.html.twig', array(
            'title' => 'Login',
            'last_email' => $lastEmail,
            'error' => $error
        ));
    }

    /**
    * @Route("/perfil", name="perfil")
    */
    public function updateUser(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(UserRegister::class, $user);

        $data['title'] = 'Perfil';

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $encoder = $this->container->get('security.password_encoder');
            $encoded = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($encoded);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->render('users/profile.html.twig', array(
            'form' => $form->createView(),
            'title' => 'Perfil',
            'msg' => 'Informações atualizadas com sucesso.'
        ));
        }

        return $this->render('users/profile.html.twig', array(
            'form' => $form->createView(),
            'title' => 'Perfil',
            'msg' => ''
        ));
    }

}