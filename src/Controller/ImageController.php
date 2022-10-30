<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/image')]
class ImageController extends AbstractController
{
    #[Route('/{id}', name: 'app_image_delete', methods: ['POST'])]
    public function delete(Request $request, Image $image, ImageRepository $imageRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $nom = $image->getUrl();
            $filesystem = new Filesystem();
            $filesystem->remove('../public/uploads/photos/'.$nom);
            
            $imageRepository->remove($image, true);
            $this->addFlash(
                'image_suppr',
                'Image supprimée avec succès!'
            );
        }

        return $this->redirectToRoute('bien_index', [], Response::HTTP_SEE_OTHER);
    }
}