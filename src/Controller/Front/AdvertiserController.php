<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Form\AdvertiserType;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// use necessaire à l'upload de fichier
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AdvertiserController extends AbstractController
{
    /**
     * @Route("/annonceur/edit/profile/{slug}", name="advertise_edit_profile", methods={"GET", "POST"})
     */
    public function editAdvertiserProfile(EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger): Response
    {

        //! ne pas s'inquièter de ce qui est souligné en rouge il n'y a pas de problème, tout fonctionne.
       
        
        // get user from session
        $user = $this->getUser();
        // todo vérifier la gestion de la mise à jour image.
        $user->setAvatarFile(null);
        $user->setBannerFile(null);
       
        dump($user);

            // if no user authenticated as advertiser, we create a new one
            if ( !$user)
            {
                $this->addFlash('danger', "vous n'êtes pas autorisé");
                $this->redirectToRoute('main_home', [], Response::HTTP_MOVED_PERMANENTLY);
            }

            // get advertiserForm and bind the authenticated user
            $form = $this->createForm(AdvertiserType::class, $user);
            $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid())
        { 

            /** @var UploadedFile avatar (ce paramètre est défini dans le form.
             *  il doit être le même dans le form et ici ex 'attachement' ou'file').
             *  je récupère le fichier image qui est uploadé dans le form
             *  sur la propriété avatar.
             **/
            

                $avatarFile = $form->get('avatar')->getData();

                // Je sette donc la valeur de $avatarFile avec le fichier image
                $user->setAvatarFile($avatarFile);
               

                // Le passage par le renommage n'est obligatoire que si une image est ajoutée 
                    // il n'est pas obligatoire d'ajouter une image si mise à jour par exemple d'autres valeurs du formulaire.
                    // Si il y a eu une image ajoutée je rentre dans ce if.
                    
                if ($avatarFile) {
                    $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                    // ici on modifie le nom du fichier pour le rendre unique et éviter les doublons ou les conflits
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$avatarFile->guessExtension();
    
                    // Ici je lui passe le repertoire de savegarde où déplacer->move() le fichier image
                        // ce rep est paramètré dans les fichiers de config dans vich_uploader.yaml et services.yaml
                        // c'est là que l'on a fait le lien entre le bundle le framework et qu'on défini les paramètre de la variable user_avatar
                        // sur qui on a paramètré le rep "path" de stockage et le chemin relatif : voir la notation avec avec %/rep/%
                    try {
                        $avatarFile->move(
                            $this->getParameter('user_avatar'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... gérer les exeptions si problème d'upload en fonction des restrictions qu'on a pu donner dans le form
                    }
    
                    // mise à jour de la propriété $avatar qui va prendre comme valeur le nouveau nom du fichier
                    $user->setAvatar($newFilename);
                   
                }

                $bannerFile = $form->get('banner')->getData();

                // Je sette donc la valeur de $bannerFile avec le fichier image
                $user->setBannerFile($bannerFile);

                // Le passage par le renommage n'est obligatoire que si une image est ajoutée 
                    // il n'est pas obligatoire d'ajouter une image si mise à jour par exemple d'autres valeurs du formulaire.
                    // Si il y a eu une image ajoutée je rentre dans ce if.
                
                if ($bannerFile) {
                    $originalFilename = pathinfo($bannerFile->getClientOriginalName(), PATHINFO_FILENAME);
                    // ici on modifie le nom du fichier pour le rendre unique et éviter les doublons ou les conflits
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$bannerFile->guessExtension();
    
                    // Ici je lui passe le repertoire de savegarde où déplacer->move() le fichier image
                        // ce rep est paramètré dans les fichiers de config dans vich_uploader.yaml et services.yaml
                        // c'est là que l'on a fait le lien entre le bundle le framework et qu'on défini les paramètre de la variable user_banner
                        // sur qui on a paramètré le rep "path" de stockage et le chemin relatif : voir la notation avec avec %/rep/%
                    try {
                        $bannerFile->move(
                            $this->getParameter('user_banner'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... gérer les exeptions si problème d'upload en fonction des restrictions qu'on a pu donner dans le form
                    }
    
                    // mise à jour de la propriété $banner qui va prendre comme valeur le nouveau nom du fichier
                    $user->setBanner($newFilename);
                    
                } 


            
            // Flash message display a success message
            $this->addFlash('success', 'votre profil a été édité');

            //dd($user); 

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('main_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/form/advertiser.html.twig', compact('form'));
    }

     /**
     * 
     * @Route("/annonceur/{slug}", name="showAdvertiserSlug")
     */
    public function showPlacePanel(EventRepository $eventRepository, UserRepository $userRepository,  string $slug): Response
    {
      
        // display advertiser page
        $user = $userRepository->findOneBy(["slug" => $slug]);

        // keep User id
        $userId = $user->getId();
        
        // display Events by user id and order by date
        $eventsList = $eventRepository->findBy(["user" => $userId],["startDate" => 'ASC'] );
        
        //dump($user);
        //dump($eventsList);

        if (!$eventsList)
        {
            throw $this->createNotFoundException('No event to display');
        }

        return $this->render('front/main/advertiser.html.twig', compact('user', 'eventsList'));
    }
}
