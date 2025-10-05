<?php

namespace App\Controller;

use App\Entity\VpsServer;
use App\Form\VpsServerType;
use App\Repository\VpsServerRepository;
use App\Repository\VpsMetricRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/vps')]
#[IsGranted('ROLE_USER')]
class VpsServerController extends AbstractController
{
    #[Route('/new', name: 'app_vps_server_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $vpsServer = new VpsServer();
        $vpsServer->setUser($this->getUser());
        
        $form = $this->createForm(VpsServerType::class, $vpsServer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($vpsServer);
            $entityManager->flush();

            $this->addFlash('success', 'Serveur VPS ajouté avec succès!');

            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('vps_server/new.html.twig', [
            'vps_server' => $vpsServer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vps_server_show', methods: ['GET'])]
    public function show(VpsServer $vpsServer, VpsMetricRepository $metricRepository): Response
    {
        $this->denyAccessUnlessGranted('view', $vpsServer);
        
        $metrics = $metricRepository->findLatestByServer($vpsServer, 24);

        return $this->render('vps_server/show.html.twig', [
            'vps_server' => $vpsServer,
            'metrics' => $metrics,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vps_server_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, VpsServer $vpsServer, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('edit', $vpsServer);
        
        $form = $this->createForm(VpsServerType::class, $vpsServer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vpsServer->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Serveur VPS modifié avec succès!');

            return $this->redirectToRoute('app_vps_server_show', ['id' => $vpsServer->getId()]);
        }

        return $this->render('vps_server/edit.html.twig', [
            'vps_server' => $vpsServer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vps_server_delete', methods: ['POST'])]
    public function delete(Request $request, VpsServer $vpsServer, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('delete', $vpsServer);
        
        if ($this->isCsrfTokenValid('delete'.$vpsServer->getId(), $request->request->get('_token'))) {
            $entityManager->remove($vpsServer);
            $entityManager->flush();

            $this->addFlash('success', 'Serveur VPS supprimé avec succès!');
        }

        return $this->redirectToRoute('app_dashboard');
    }
}
