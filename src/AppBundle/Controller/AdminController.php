<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\AdminRegister;
use AppBundle\Form\AdminUpdateUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    /**
     * @Route("/admin/newUser", name="newUser")
     */
    public function newUser(Request $request)
    {
        $title = "Novo usuário";
        
        $user = new User();
        $form = $this->createForm(AdminRegister::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            if($user->getRoles() == "ROLE_ADMIN"){
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }

            $encoder = $this->container->get('security.password_encoder');
            $encoded = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($encoded);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('listUser');
        }

        return $this->render('admin/register.html.twig', array(
            'form' => $form->createView(),
            'title' => $title
        ));
    }

    /**
     * @Route("/admin/listUsers", name="listUser")
     */
    public function listUsers(Request $request) 
    {
        $data['title'] = 'Usuários';

        $reqUser = $request->get('search');
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(User::class);

        $data['users'] = is_null($reqUser) ? $repository->findAll() : $repository->findBySearch($reqUser);

        foreach ($data['users'] as $user) {

            $roles = $user->getRoles();
            
            if($roles[0] == "ROLE_ADMIN") {
                $user->user_type = 'Administrador';
            } else if($roles[0] == "ROLE_USER") {
                $user->user_type = 'Usuário';
            }
        }

        return $this->render('admin/index.html.twig', $data);
    }

    /**
     * @Route("/admin/deleteUser/{id}", name="deleteUser")
     */
    public function delete($id, Request $request)
    {
        $data['title'] = 'Exclui usuário';

        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('listUser');
    }

    /**
     * @Route("/admin/updateUser/{id}", name="adminUpdateUser")
     */
    public function updateUser($id, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        $form = $this->createForm(AdminUpdateUser::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $userSubmit = $form->getData();
            
            if (in_array("ROLE_ADMIN", $userSubmit->getRoles())) {
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }

            $encoder = $this->container->get('security.password_encoder');
            $encoded = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($encoded);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('listUser');
        }

        return $this->render('admin/updateUser.html.twig', array(
            'form' => $form->createView(),
            'title' => 'Atualiza usuário'
        ));
    }
}