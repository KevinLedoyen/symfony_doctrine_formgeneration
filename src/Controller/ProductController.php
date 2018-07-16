<?php
// src/Controller/ProductController.php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use App\Entity\Product;

use Doctrine\ORM\EntityManagerInterface;

class ProductController extends Controller
{
     /**
     * @Route("/product", name="product")
     */
    // public function index(EntityManagerInterface $entityManager)
    public function index()
    {
        //vous pouvez récupérer  l'EntityManager via $this->getDoctrine()
        // ou vous pouvez ajouter un argument EntityManagerInterface à votre fonction 
        //index(EntityManagerInterface $entityManager)
        $entityManager = $this->getDoctrine()->getManager();

        $product = new Product();
        $product->setName('Keyboard');
        $product->setPrice(1999);
        $product->setDescription('Ergonomique et stylé!');

        // avertit doctrine qu'eventuellement on va sauvegarder le produit 
        // mais la requête n'est pas encore exécutée
        $entityManager->persist($product);

        // Exécute réellement la requête (c'est à dire la requête insert)
        $entityManager->flush();

        return new Response('Un nouveau produit est inséré '.$product->getId());
    }

    /**
     * @Route("/product/{id}", name="product_show")
     */
    public function show($id)
    {
        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        return new Response('Voici un grand produit: '.$product->getName());

        // ou faites un render sur un template
        //dans le template , affichez le nom grâce à {{ product.name }}
        // return $this->render('product/show.html.twig', ['product' => $product]);
    }
}