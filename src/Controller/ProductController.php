<?php
// src/Controller/ProductController.php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Product;

// utilisation de l'entity manager
use Doctrine\ORM\EntityManagerInterface;

// utilisation du générateur de formulaire
use Symfony\Component\Form\Forms;

// ajout des types d'inputs : https://api.symfony.com/3.1/Symfony/Component/Form/Extension/Core/Type.html
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProductController extends Controller
{
	/**
	* @Route("/product", name="product")
	*/
    public function index(Request $request, EntityManagerInterface $entityManager)
    // public function index()
    {
        //vous pouvez récupérer  l'EntityManager via $this->getDoctrine()
        // ou vous pouvez ajouter un argument EntityManagerInterface à votre fonction 
        //index(EntityManagerInterface $entityManager)
        $entityManager = $this->getDoctrine()->getManager();


        return new Response('liste des produits ');
    }

    	/**
    	* @Route("/product/new", name="product_add")
    	*/
        public function new(Request $request, EntityManagerInterface $entityManager)
        // public function index()
        {
            //vous pouvez récupérer  l'EntityManager via $this->getDoctrine()
            // ou vous pouvez ajouter un argument EntityManagerInterface à votre fonction 
            //index(EntityManagerInterface $entityManager)
            $entityManager = $this->getDoctrine()->getManager();

            // Création d'un nouveau produit
            $product = new Product();

            /* FORMULAIRE */
            $form = $this->createFormBuilder($product)
            ->add('name', TextType::class)
            ->add('price', TextType::class)
            ->add('Description', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Create Task'))
            ->getForm();

            /* SAVE */
            // $product->setName('Keyboard');
            // $product->setPrice(1999);
            // $product->setDescription('Ergonomique et stylé!');

            // avertit doctrine qu'eventuellement on va sauvegarder le produit 
            // mais la requête n'est pas encore exécutée
            // $entityManager->persist($product);

            // Exécute réellement la requête (c'est à dire la requête insert)
            // $entityManager->flush();

            $form->handleRequest($request);
	        if ($form->isSubmitted() && $form->isValid()) {
				// $form->getData() holds the submitted values
				// but, the original `$product` variable has also been updated
	        	$product = $form->getData();
				// ... perform some action, such as saving the product to the database
				// for example, if Product is a Doctrine entity, save it!
				$entityManager = $this->getDoctrine()->getManager();
				$entityManager->persist($product);
				$entityManager->flush();

	        	return $this->redirectToRoute('product_show', [
	        		'id' => $product->getId()
	        	]);
	        }
            return $this->render('default/new.html.twig', array(
            	'form' => $form->createView(),
            ));
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

	/**
	* @Route("/product/edit/{id}")
	*/
	public function update(Request $request, $id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$product = $entityManager
			->getRepository(Product::class)
			->find($id);

		if (!$product) {
			throw $this->createNotFoundException(
				'No product found for id '.$id
			);
		}

        /* FORMULAIRE */
        $form = $this->createFormBuilder($product)
        ->add('name', TextType::class)
        ->add('price', TextType::class)
        ->add('Description', TextType::class)
        ->add('save', SubmitType::class, array('label' => 'Create Task'))
        ->getForm();

		$form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
			// $form->getData() holds the submitted values
			// but, the original `$product` variable has also been updated
        	$product = $form->getData();
			// ... perform some action, such as saving the product to the database
			// for example, if Product is a Doctrine entity, save it!
			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($product);
			$entityManager->flush();

        	return $this->redirectToRoute('product_show', [
        		'id' => $product->getId()
        	]);
        }


        return $this->render('default/new.html.twig', array(
        	'form' => $form->createView(),
        ));
	}

	/**
	* @Route("/product/delete/{id}")
	*/
	public function delete($id)
	{
		$entityManager = $this->getDoctrine()->getManager();
		$product = $entityManager->getRepository(Product::class)->find($id);

		if (!$product) {
			throw $this->createNotFoundException(
				'No product found for id '.$id
			);
		}

		$entityManager->remove($product);
		$entityManager->flush();

		return new Response("supprime id:".$id);
	}
}