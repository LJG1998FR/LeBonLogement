<?php

namespace App\Controller;

use App\Entity\Bien;
use App\Entity\Image;
use App\Form\BienType;
use App\Repository\BienRepository;
use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/bien')]
class BienController extends AbstractController
{
    #[Route('/', name: 'app_bien_index', methods: ['GET'])]
    public function index(BienRepository $bienRepository, ImageRepository $imageRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('bien/index.html.twig', [
            'biens' => $bienRepository->findAll(),
            'images' => $imageRepository->findAll(),
        ]);
    }

    #[Route('/all', name: 'bien_all_index', methods: ['GET'])]
    public function indexall(BienRepository $bienRepository, ImageRepository $imageRepository): Response
    {
        return $this->render('bien/indexall.html.twig', [
            'biens' => $bienRepository->findAll(),
            'images' => $imageRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_bien_new', methods: ['GET', 'POST'])]
    public function new(Request $request, BienRepository $bienRepository, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $bien = new Bien();
        $form = $this->createForm(BienType::class, $bien);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $photoFiles */
            $photoFiles = $form->get('images')->getData();

            if($photoFiles){
                foreach ($photoFiles as $pic) {
                    $originalFilename = pathinfo($pic->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'.'.uniqid().'.'.$pic->guessExtension();
    
                    try {
                        $pic->move(
                            $this->getParameter('photos_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }

                    $newimage = new Image();    
                    $newimage->setUrl($newFilename);
                    $bien->addImage($newimage);
                }
            }
            $bien->setProprietaire($this->getUser());
            $bienRepository->save($bien, true);
            $this->addFlash(
                'nouveau_bien',
                'Nouveau bien ajouté!'
            );

            return $this->redirectToRoute('app_bien_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('bien/new.html.twig', [
            'bien' => $bien,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bien_show', methods: ['GET'])]
    public function show(Bien $bien): Response
    {
        return $this->render('bien/show.html.twig', [
            'bien' => $bien,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_bien_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Bien $bien, BienRepository $bienRepository): Response
    {
        if ( $bien->getProprietaire() != $this->getUser() ) {
            return $this->redirectToRoute('app_bien_index');
        }
        $form = $this->createForm(BienType::class, $bien);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $photoFiles */
            $photoFiles = $form->get('images')->getData();

            if($photoFiles){
                foreach ($photoFiles as $pic) {
                    $originalFilename = pathinfo($pic->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'.'.uniqid().'.'.$pic->guessExtension();
    
                    try {
                        $pic->move(
                            $this->getParameter('photos_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }

                    $newimage = new Image();    
                    $newimage->setUrl($newFilename);
                    $bien->addImage($newimage);
                }
            }
            $bienRepository->save($bien, true);
            $this->addFlash(
                'modif_bien',
                'Bien modifié!'
            );

            return $this->redirectToRoute('app_bien_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('bien/edit.html.twig', [
            'bien' => $bien,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_bien_delete', methods: ['POST'])]
    public function delete(Request $request, Bien $bien, BienRepository $bienRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$bien->getId(), $request->request->get('_token'))) {
            $bienRepository->remove($bien, true);
        }

        return $this->redirectToRoute('app_bien_index', [], Response::HTTP_SEE_OTHER);
    }
}
