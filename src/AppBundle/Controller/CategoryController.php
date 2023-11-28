<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Form\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends Controller
{

    /**
    * @Route("/admin/categories", name="categories")
    */
    public function categories(Request $request)
    {
        $data['title'] = 'Categorias';

        $reqCategory = $request->get('search');
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(Category::class);

        $data['categories'] = is_null($reqCategory) ? $repository->findAll() : $repository->findBySearch($reqUser);

        return $this->render('companies/category/index.html.twig', $data);
    }

    /**
    * @Route("/admin/newCategory", name="newCategory")
    */
    public function newCategory(Request $request)
    {
        $msgSuccess = "";
        $msgError = "";

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('categories');
        }

        return $this->render('companies/category/newCategory.html.twig', array(
            'form' => $form->createView(),
            'title' => 'Nova categoria'
        ));
    }

    /**
    * @Route("/admin/deleteCategory/{id}", name="deleteCategory")
    */
    public function delete($id, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = $entityManager->getRepository(Category::class)->find($id);

        $entityManager->remove($category);
        $entityManager->flush();

        return $this->redirectToRoute('categories');
    }

    /**
    * @Route("/admin/updateCategory/{id}", name="updateCategory")
    */
    public function update($id, Request $request)
    {
        $data['title'] = 'Atualizar categoria';

        $entityManager = $this->getDoctrine()->getManager();
        $category = $entityManager->getRepository(Category::class)->find($id);

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            $data['msg'] = 'Categoria atualizada';
            $data['form'] = $form->createView();

            return $this->render('companies/category/update.html.twig', $data);
        }

        $data['form'] = $form->createView();
        $data['msg'] = '';

        return $this->render('companies/category/update.html.twig', $data);
    }
}