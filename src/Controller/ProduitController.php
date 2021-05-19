<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Images;
use App\Entity\Produit;
use App\Form\ProduitType;
use App\Entity\PriceSearch;
use App\Entity\MatiereSearch;
use App\Form\PriceSearchType;
use App\Entity\PropertySearch;
use App\Form\RegistrationType;
use App\Form\MatiereSearchType;
use App\Form\PropertySearchType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/")
 */
class ProduitController extends AbstractController
{

/**
 * @Route("/deconnexion",name="security_logout")
*/
public function logout()
{
    return $this->redirectToRoute("/");
 }
/**
 * @Route("/",name="security_login")
 */
public function login(AuthenticationUtils  $authenticationUtils)
{
     // get the login error if there is one
$error = $authenticationUtils->getLastAuthenticationError();

// last username entered by the user
$lastUsername = $authenticationUtils->getLastUsername();
    
    return $this->render('security/login.html.twig',['lastUsername'=>$lastUsername,
                                                    'error' => $error]);
}


/**
 * @Route("/inscription", name="inscri")
 */
public function registration(Request $request, EntityManagerInterface $em , UserPasswordEncoderInterface $encoder)
{
$user = new User();
$form = $this->createForm(RegistrationType::class, $user);
$form->handleRequest($request);

if($form->isSubmitted() && $form->isValid()) {
   $hash = $encoder->encodePassword($user,$user->getPassword());
   $user->setPassword($hash);
  
//l'objet $em sera affecté automatiquement grâce à l'injection des dépendances de symfony 4
$em->persist($user);
$em->flush();
return $this->redirectToRoute('inscri');
}
return $this->render('security/registration.html.twig',
['form' =>$form->createView()]);
}
    /**
     * @Route("/art_prix/", name="article_par_prix")
     * Method({"GET"})
     */
    public function ProduitParPrix(Request $request)
    {
     
      $priceSearch = new PriceSearch();
      $form = $this->createForm(PriceSearchType::class,$priceSearch);
      $form->handleRequest($request);

      $produits= [];

      if($form->isSubmitted() && $form->isValid()) {
        $minPrice = $priceSearch->getMinPrice(); 
        $maxPrice = $priceSearch->getMaxPrice();
          
        $produits= $this->getDoctrine()->getRepository(Produit::class)->findByPriceRange($minPrice,$maxPrice);
    }

    return  $this->render('Produit/produitParPrix.html.twig',[ 'form' =>$form->createView(), 'produits' => $produits]);  
  }



      /**
     * @Route("/lister1", name="lister1")
     * Method({"GET", "POST"})
     */
    public function lister1(Request $request) {
        $MatiereSearch= new MatiereSearch();
        $form = $this->createForm(MatiereSearchType::class,$MatiereSearch);
        $form->handleRequest($request);
  
        $produits= [];
  
        if($form->isSubmitted() && $form->isValid()) {
          $matier = $MatiereSearch->getMatiere();
         
          if ($matier!="") 
          {
            $produits= $matier->getProduits();
          }
          else   
            $produits= $this->getDoctrine()->getRepository(Produit::class)->findAll();
          }
        
          return $this->render('produit/listMat.html.twig', [ 'form' =>$form->createView(), 'produits' => $produits]);
        }
    /**
     * @Route("/lister", name="lister")
     */
    public function lister(Request $request)
    {
     $propertySearch = new PropertySearch();
      $form = $this->createForm(PropertySearchType::class,$propertySearch);
      $form->handleRequest($request);
     //initialement le tableau des articles est vide, 
     //c.a.d on affiche les articles que lorsque l'utilisateur clique sur le bouton rechercher
      $produits= [];
      
     if($form->isSubmitted() && $form->isValid()) {
     //on récupère le nom d'article tapé dans le formulaire
      $titre = $propertySearch->getTitre();   
      if ($titre!="") 
        //si on a fourni un nom d'article on affiche tous les articles ayant ce nom
        $produits= $this->getDoctrine()->getRepository(Produit::class)->findBy(['titre' => $titre] );
      else   
        //si si aucun nom n'est fourni on affiche tous les articles
        $produits= $this->getDoctrine()->getRepository(Produit::class)->findAll();
     }
     return $this->render('produit/lister.html.twig', [ 'form' =>$form->createView(), 'produits' => $produits]);
    }
    /**
     * @Route("/liste", name="produit_index", methods={"GET"})
     */
    public function index(Request $request, PaginatorInterface $paginator,ProduitRepository $produitRepository): Response
    {
        $donnees= $this->getDoctrine()->getRepository(Produit::class)->findAll();
        $produits = $paginator->paginate(
            $donnees, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            6 // Nombre de résultats par page
        );
        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
        ]);
    }
 
    /**
     * @Route("/new", name="produit_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                // On récupère les images transmises
               $images = $form->get('images')->getData();
    
                // On boucle sur les images
                  foreach($images as $image){
                  // On génère un nouveau nom de fichier
                   $fichier = md5(uniqid()).'.'.$image->guessExtension();
        
                  // On copie le fichier dans le dossier uploads
                 $image->move(
                   $this->getParameter('images_directory'),
                    $fichier
                     );
        
                // On crée l'image dans la base de données
                    $img = new Images();
                    $img->setName($fichier);
                     $produit->addImage($img);
    }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('produit_index');
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="produit_show", methods={"GET"})
     */
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="produit_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Produit $produit): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                           // On récupère les images transmises
                           $images = $form->get('images')->getData();
    
                           // On boucle sur les images
                             foreach($images as $image){
                             // On génère un nouveau nom de fichier
                              $fichier = md5(uniqid()).'.'.$image->guessExtension();
                   
                             // On copie le fichier dans le dossier uploads
                            $image->move(
                              $this->getParameter('images_directory'),
                               $fichier
                                );
                   
                           // On crée l'image dans la base de données
                               $img = new Images();
                               $img->setName($fichier);
                                $produit->addImage($img);
               }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('produit_index');
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="produit_delete", methods={"POST"})
     */
    public function delete(Request $request, Produit $produit): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('produit_index');
    }
/**
 * @Route("/supprime/image/{id}", name="produit_delete_image", methods={"DELETE"})
 */
public function deleteImage(Images $image, Request $request){
    $data = json_decode($request->getContent(), true);

    // On vérifie si le token est valide
    if($this->isCsrfTokenValid('delete'.$image->getId(), $data['_token'])){
        // On récupère le nom de l'image
        $nom = $image->getName();
        // On supprime le fichier
        unlink($this->getParameter('images_directory').'/'.$nom);

        // On supprime l'entrée de la base
        $em = $this->getDoctrine()->getManager();
        $em->remove($image);
        $em->flush();

        // On répond en json
        return new JsonResponse(['success' => 1]);
    }else{
        return new JsonResponse(['error' => 'Token Invalide'], 400);
    }
 }   
 


}
