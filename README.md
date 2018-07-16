https://symfony.com/doc/current/doctrine.html   -> paragraphe doctrine
 
			DOCTRINE

Installation de Doctrine ¶
Tout d'abord, installez la prise en charge de Doctrine via le pack ORM, ainsi que MakerBundle, ce qui vous aidera à générer du code:

///////////////////////////////////////////////////////////
composer require symfony/orm-pack
composer require doctrine
//////////////////////////////////////////////////////////

Si vous ne l'avez pas encore installé, MakerBundle, ce qui vous aidera à générer du code:
composer require symfony/maker-bundle --dev
/////////////////////////////////////////////////////////////

Configuration de la base de données ¶
Les informations de connexion à la base de données sont stockées en tant que variable d'environnement appelée  DATABASE_URL. 
Pour le développement, vous pouvez trouver et personnaliser ceci à l'intérieur .env:

3 dernières lignes
//////////////////////////////////////////////////////////////////
# Configure your db driver and server_version in config/packages/doctrine.yaml
DATABASE_URL=mysql://root:@127.0.0.1:3306/mondialdoc
###< doctrine/doctrine-bundle ###
//////////////////////////////////////////////////////////////////

Maintenant que vos paramètres de connexion sont configurés, Doctrine peut créer la db_name base de données pour vous:
//////////////////////////////////////////////////////////////////
 php bin/console doctrine:database:create
//////////////////////////////////////////////////////////////////

Il y a plus d'options dans config/packages/doctrine.yaml que vous pouvez configurer, 
y compris votre server_version(par exemple 5.7 si vous utilisez MySQL 5.7), 
ce qui peut affecter le fonctionnement de Doctrine.


Créer une classe entité ¶
Supposons que vous construisiez une application dans laquelle les produits doivent être affichés. 
Sans même penser à Doctrine ou aux bases de données, vous savez déjà que vous avez besoin d'un objet Product pour représenter ces produits.

Vous pouvez utiliser commande la make:entity  pour créer cette classe et tous les champs dont vous avez besoin. 
//////////////////////////////////////////////////////////////////
php bin/console make:entity
//////////////////////////////////////////////////////////////////
La commande vous posera quelques questions - répondez-y comme fait ci-dessous:


//////////////////////////////////////////////////////////////////
Class name of the entity to create or update:
> Product

 to stop adding fields):
> name

Field type (enter ? to see all types) [string]:
> string

Field length [255]:
> 255

Can this field be null in the database (nullable) (yes/no) [no]:
> no

 to stop adding fields):
> price

Field type (enter ? to see all types) [string]:
> integer

Can this field be null in the database (nullable) (yes/no) [no]:
> no

 to stop adding fields):
>
(press enter again to finish)
//////////////////////////////////////////////////////////////////

Vous avez maintenant un nouveau fichier src/Entity/Product.php:

Cette classe est appelée une "entité". 
Et bientôt, vous pourrez enregistrer et interroger les objets Product sur une table product de votre base de données. 
Chaque propriété de l'entité Product  peut être mappée à une colonne de cette table.
Cela se fait généralement avec des annotations: les @ORM\...commentaires que vous voyez au-dessus de chaque propriété
 
 
 Migrations: Création des tables / schémas de base de données 
La classe Product est entièrement configurée et prête à enregistrer dans une table product. 
Bien sûr, votre base de données n'a pas encore la table product. 
Pour l'ajouter, vous pouvez tirer parti de DoctrineMigrationsBundle , qui est déjà installé:

//////////////////////////////////////////////////////////////////
 php bin/console make:migration
//////////////////////////////////////////////////////////////////
 
Si tout fonctionnait, vous devriez voir quelque chose comme ceci:

SUCCÈS!

regardez la nouvelle migration "src / Migrations / Version20180207231217.php" 
Si vous ouvrez ce fichier, il contient le code SQL nécessaire pour mettre à jour votre base de données! 
Pour exécuter ce SQL, exécutez vos migrations:

//////////////////////////////////////////////////////////////////
 php bin/console doctrine:migrations:migrate
 //////////////////////////////////////////////////////////////////
 
 Cette commande exécute tous les fichiers de migration qui n'ont pas encore été exécutés sur votre base de données. 
 Vous devez exécuter cette commande sur la production lorsque vous déployez pour maintenir votre base de données de production à jour

Migrations et ajout de plusieurs champs ¶
Mais que faire si vous avez besoin d'ajouter une nouvelle propriété de champ à Product, comme un description? 
Il est facile d'ajouter la nouvelle propriété à la main. 
Mais, vous pouvez également utiliser à nouveau
//////////////////////////////////////////////////////////////////
php bin/console make:entity
//////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////
Class name of the entity to create or update
> Product

 to stop adding fields):
> description

Field type (enter ? to see all types) [string]:
> text

Can this field be null in the database (nullable) (yes/no) [no]:
> no

 to stop adding fields):
>
(press enter again to finish)
////////////////////////////////////////////////////////////////////

Cela ajoute la nouvelle propriété  description et les méthodes getDescription()et  setDescription()  dans src/Entity/Product.php

La nouvelle propriété est mappée, mais elle n'existe pas encore dans la producttable. Aucun problème! Générez simplement une nouvelle migration:
```
php bin/console make:migration
```
Passez en revue la nouvelle migration "src / Migrations / Version20180207231217.php" 
Si vous ouvrez ce fichier, il contient le code SQL nécessaire pour mettre à jour votre base de données! 
Pour exécuter ce SQL, exécutez vos migrations:
```
 php bin/console doctrine:migrations:migrate
 ```
 
 Si vous préférez ajouter de nouvelles propriétés manuellement, la commande make:entity peut générer les méthodes getter et setter pour vous:
```
 php bin/console make:entity --regenerate
 ```
Si vous apportez des modifications et souhaitez régénérer toutes les méthodes getter / setter, passez également --overwrite.

Pour modifier une colonne : on modifie dans la classe correspondante dans App/Entity le nom de la colonne (attention aux getters et setters correspondants)
Puis on lance les deux commandes suivantes pour mettre à jour
```
php bin/console make:migration
php bin/console doctrine:migrations:migrate 
```


Objets persistants dans la base de données ¶
Il est temps de sauvegarder un objet Product dans la base de données! Créons un nouveau contrôleur pour expérimenter:
```
php bin/console make:controller ProductController
```
  
 À l'intérieur du contrôleur, vous pouvez créer un nouvel Productobjet, y définir des données et l'enregistrer!
 
```
// src/Controller/ProductController.php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use App\Entity\Product;

//use Doctrine\ORM\EntityManagerInterface;

class ProductController extends Controller
{
     /**
     * @Route("/product", name="product")
     */
    public function index(EntityManagerInterface $entityManager)
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
}
```

http://localhost:8000/product
cela ajoute une ligne à la table

vérifions :
```
php bin/console doctrine:query:sql 'SELECT * FROM product'
```

Récupérer des objets à partir de la base de données ¶
Récupérer un objet venant  de la base de données est encore plus facile. 
Supposons que vous vouliez pouvoir voir  votre nouveau produit: 
avec la route /product/1 

ajoutons dans le controller 
////////////////////////////////////////////////////////////////////
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
////////////////////////////////////////////////////////////////////////
http://localhost:8000/product/1
 cela affiche le nom de la 1ere ligne de la table
 
 
 Supposons que vous vouliez modifier votre nouveau produit: 
avec la route /product/edit/1 

 ////////////////////////////////////////////////////////////////////////
 /**
	 * @Route("/product/edit/{id}")
	 */
	public function update($id)
	{
	    $entityManager = $this->getDoctrine()->getManager();
	    $product = $entityManager->getRepository(Product::class)->find($id);



	    if (!$product) {
	        throw $this->createNotFoundException(
	            'No product found for id '.$id
	        );
	    }

	    $product->setName('New product name!');
	    $entityManager->flush();

	    return $this->redirectToRoute('product_show', [
	        'id' => $product->getId()
	    ]);
	}
////////////////////////////////////////////////////////////////////////
 http://localhost:8000/product/edit/1
 cela modifie  le nom de la 1ere ligne de la table
 
 
 
 Supposons que vous vouliez supprimer votre nouveau produit: 
avec la route /product/delete/1 
 
 ////////////////////////////////////////////////////////////////////////
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
  ////////////////////////////////////////////////////////////////////////
  http://localhost:8000/product/delete/1
 cela supprimer   la 1ere ligne de la table
 
 
 
 

 Lorsque vous effectuez une requête pour un type d'objet particulier, vous utilisez toujours ce qu'on appelle son "référentiel".
 Vous pouvez considérer un référentiel comme une classe PHP dont le seul travail est de vous aider à récupérer des entités 
 d'une certaine classe.

Une fois que vous avez un objet de référentiel, vous avez plusieurs méthodes d'assistance:
 

////////////////////////////////////////////////////////////////////////
DANS LE FICHIER REPOSITORY

$repository = $this->getDoctrine()->getRepository(Product::class);

// look for a single Product by its primary key (usually "id")
$product = $repository->find($id);

// look for a single Product by name
$product = $repository->findOneBy(['name' => 'Keyboard']);
// or find by name and price
$product = $repository->findOneBy([
    'name' => 'Keyboard',
    'price' => 1999,
]);

// look for multiple Product objects matching the name, ordered by price
$products = $repository->findBy(
    ['name' => 'Keyboard'],
    ['price' => 'ASC']
);

// look for *all* Product objects
$products = $repository->findAll();
////////////////////////////////////////////////////////////////////////

Vous pouvez également ajouter des méthodes personnalisées pour des requêtes plus complexes dans ProductRepository
Plus d'informations à ce sujet plus tard dans la section Querying for Objects: The Repository 
https://symfony.com/doc/current/doctrine.html#doctrine-queries

						 EN SQL 
```
DANS LE FICHIER REPOSITORY
// src/Repository/ProductRepository.php
  /**
     * @param $price
     * @return Product[]
     */
public function findAllGreaterThanPrice($price): array
{
    $conn = $this->getEntityManager()->getConnection();

    $sql = '
        SELECT * FROM product p
        WHERE p.price > :price
        ORDER BY p.price ASC
        ';
    $stmt = $conn->prepare($sql);
    $stmt->execute(['price' => 1000]);

    // returns an array of arrays (i.e. a raw data set)
    return $stmt->fetchAll();
}
```
 On l'appelle dans un controller liste
```
 /**
     * @Route("/liste")
     */
    public function liste()
    {

        $liste='';
        $produits= $this->getDoctrine()->getRepository(Product::class)->findAllGreaterThanPriceSQL(2000);
        foreach ($produits as $produit) {
            $liste.=$produit["name"].'<br>';
        }
 
        return new Response("Liste:".$liste);

    }
```


EN DQL
```
DANS LE FICHIER REPOSITORY
// src/Repository/ProductRepository.php
  /**
     * @param $price
     * @return Product[]
     */
    public function findAllGreaterThanPrice($price): array
    {
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.price > :price')
            ->setParameter('price', $price)
            ->orderBy('p.price', 'ASC')
            ->getQuery();

        return $qb->execute();

        // to get just one result:
        // $product = $qb->setMaxResults(1)->getOneOrNullResult();
    }
```
 On l'appelle dans le controller de show
 
```
$liste='';
 $produits= $this->getDoctrine()->getRepository(Product::class)->findAllGreaterThanPrice(2000);
foreach ($produits as $produit) {
	$liste.=$produit->getName().'<br>';
}
echo $liste;
```