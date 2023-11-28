<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Companies;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

class ListCompaniesController extends Controller
{
    /**
     * @Route("/listCompanies", name="listCompanies")
     */
    public function listCompanies(Request $request)
    {
        $title = "Lista de empresas";
        $reqCompany = $request->get('search');

        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(Companies::class);

        $data['companies'] = is_null($reqCompany) ? $repository->findAll() : $repository->findBySearch($reqCompany);
        $data['title'] = $title;

        return $this->render('companies/index.html.twig', $data);
    }

    /**
     * @Route("/", name="searchCompanies")
     */
    public function searchCompanies()
    {
        return $this->render('companies/search.html.twig', array(
            'title' => 'Buscar empresas'
        ));
    }

    /**
     * @Route("/newCompany", name="newCompany")
     */
    public function creatingCompanies(Request $request) 
    {
        $title = "Adicionar nova empresa.";
        $msgSuccess = "";

        $company = new Companies();

        $form = $this->createFormBuilder($company)
            ->add('titulo', TextType::class, ['label' => 'Nome da empresa'])
            ->add('telefone', NumberType::class, ['label' => 'Telefone'])
            ->add('endereco', TextType::class, ['label' => 'Endereço'])
            ->add('cep', NumberType::class, ['label' => 'Cep'])
            ->add('cidade', TextType::class, ['label' => 'Cidade'])
            ->add('estado', TextType::class, ['label' => 'Estado'])
            ->add('categories', EntityType::class, [
                'class' => 'AppBundle\Entity\Category',
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Categorias'
            ])
            ->add('descricao', TextareaType::class, ['label' => 'Descrição'])
            ->add('Adicionar', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() ) {
            $company = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($company);
            $entityManager->flush();

            $msgSuccess = "Empresa adicionada com sucesso.";
        }    

        return $this->render('companies/newCompany.html.twig', array(
            'form' => $form->createView(),
            'msgSuccess' => $msgSuccess,
            'title' => $title
        ));
    }
    
    /**
     * @Route("/searchCompany/{id}", name="searchCompany")
     */
    public function searchCompany($id, Request $request) {
        $msgError = "";

        try {
            $data['title'] = "Informações da empresa.";

            $reqCompany = $request->get('search');

            $entityManager = $this->getDoctrine()->getManager();
            $repository = $entityManager->getRepository(Companies::class);
            $data['company'] = $repository->find($id);
        } catch (\Exception $e) {
            $msgError = "Erro ao buscar as informações da empresa: ".$e->getMessage();
        }
        
        $data['msgError'] = $msgError;

        return $this->render('companies/searchCompany.html.twig', $data);
    }

    /**
    * @Route("/deleteCompany/{id}", name="deleteCompany")
    */
    public function deleteCompany($id, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $company = $entityManager->getRepository(Companies::class)->find($id);

        $entityManager->remove($company);
        $entityManager->flush();

        return $this->redirectToRoute('listCompanies');
    }
}