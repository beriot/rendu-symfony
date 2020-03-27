<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Form\ProduitType;
use App\Form\PanierType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\File\File;


class PanierController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request , EntityManagerInterface )
    {
        $panierRepository = $this->getDoctrine()->getRepository(Panier::class)
            ->findAll();
        $produitRepository = $this->getDoctrine()->getRepository(Produit::class)
            ->findAll();


        return $this->render('panier/index.html.twig', [
            'panier' => $panierRepository,
            'produit' => $produitRepository,


        ]);
    }


    /**
     * @Route("/produits", name="produits")
     */
    public function produits (Request $request, EntityManagerInterface $entityManager)
    {
        $produit = new Produit();

        $produitRepository = $this->getDoctrine()
            ->getRepository(produit::class)
            ->findAll();

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $produit = $form->getData();

           $photo = $produit->getPhoto();
           $photoName = md5(uniqid()) . '.' . $photo->guessExtension();
           $photo->move($this->getParameter('upload_files'),
               $photoName);
            $produit->setPhoto($photoName);


            $entityManager->persist($produit);
            $entityManager->flush();
        }

        return $this->render('panier/produits.html.twig', [
            'produit' => $produitRepository,
            'formProduit' => $form->createView()
        ]);

    }

    /**
     * @Route("/panier/produitSingle/{{id}}", name="produitSingle")
     */
    public function produitSingle( $id, Request $request, EntityManagerInterface $entityManager)
    {

        $produitRepository = $this->getDoctrine()
            ->getRepository(Produit::class)
            ->find($id);

        $panier = new panier();

        $panierRepository = $this->getDoctrine()
            ->getRepository(panier::class)
            ->findAll();

        $produitList = $this->getDoctrine()
            ->getRepository(produit::class)
            ->findAll();


        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $panier = $form->getData();

            $produits = $this->getDoctrine()
                ->getRepository(produit::class)
                ->find($request->request->get('produitId'));
            $panier->setProduitId($produits);

            $entityManager->persist($panier);
            $entityManager->flush();
        }



        return $this->render('panier/produitSingle.html.twig', [
            'produits' => $produitRepository,
            'paniers' => $panierRepository,
            'formPanier' => $form->createView(),
            'lists' => $produitList

        ]);

    }
    /**
     * @Route("/deleteProduit/{id}", name="deleteProduit")
     */
    public function deleteProduit($id, EntityManagerInterface $entityManager){
        $produit = $this->getDoctrine()->getRepository(Produit::class)->find($id);
        $produit->deleteFile();

        $entityManager->remove($produit);
        $entityManager->flush();

        return $this->redirectToRoute("produits");


    }
}