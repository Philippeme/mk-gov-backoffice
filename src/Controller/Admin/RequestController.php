<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RequestController extends AbstractController
{
    #[Route('/admin/requests', name: 'admin_requests')]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);
        $search = $request->query->get('search', '');
        $status = $request->query->get('status', 'all');
        $type = $request->query->get('type', 'all');

        // Données simulées - à remplacer par des requêtes réelles
        $requests = $this->getRequestsList($page, $limit, $search, $status, $type);
        $totalRequests = $this->getTotalRequestsCount($search, $status, $type);

        return $this->render('admin/requests/index.html.twig', [
            'requests' => $requests,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalRequests / $limit),
                'total_items' => $totalRequests,
                'limit' => $limit
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'type' => $type
            ],
            'statuses' => $this->getStatusesList(),
            'types' => $this->getRequestTypesList()
        ]);
    }

    #[Route('/admin/requests/{id}', name: 'admin_requests_show')]
    public function show(int $id): Response
    {
        $request = $this->getRequestById($id);
        
        if (!$request) {
            throw $this->createNotFoundException('Request not found');
        }

        return $this->render('admin/requests/show.html.twig', [
            'request' => $request,
            'timeline' => $this->getRequestTimeline($id),
            'documents' => $this->getRequestDocuments($id),
            'comments' => $this->getRequestComments($id)
        ]);
    }

    #[Route('/admin/requests/new', name: 'admin_requests_new')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Traitement du formulaire de création
            $data = $request->request->all();
            $newRequestId = $this->createRequest($data);
            
            $this->addFlash('success', 'Request created successfully');
            return $this->redirectToRoute('admin_requests_show', ['id' => $newRequestId]);
        }

        return $this->render('admin/requests/new.html.twig', [
            'request_types' => $this->getRequestTypesList(),
            'public_entities' => $this->getPublicEntitiesList(),
            'citizens' => $this->getCitizensList()
        ]);
    }

    #[Route('/admin/requests/{id}/edit', name: 'admin_requests_edit')]
    public function edit(int $id, Request $request): Response
    {
        $existingRequest = $this->getRequestById($id);
        
        if (!$existingRequest) {
            throw $this->createNotFoundException('Request not found');
        }

        if ($request->isMethod('POST')) {
            // Traitement du formulaire de modification
            $data = $request->request->all();
            $this->updateRequest($id, $data);
            
            $this->addFlash('success', 'Request updated successfully');
            return $this->redirectToRoute('admin_requests_show', ['id' => $id]);
        }

        return $this->render('admin/requests/edit.html.twig', [
            'request' => $existingRequest,
            'request_types' => $this->getRequestTypesList(),
            'public_entities' => $this->getPublicEntitiesList(),
            'statuses' => $this->getStatusesList()
        ]);
    }

    #[Route('/admin/requests/{id}/delete', name: 'admin_requests_delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        $request = $this->getRequestById($id);
        
        if (!$request) {
            throw $this->createNotFoundException('Request not found');
        }

        $this->deleteRequest($id);
        $this->addFlash('success', 'Request deleted successfully');
        
        return $this->redirectToRoute('admin_requests');
    }

    #[Route('/admin/requests/{id}/approve', name: 'admin_requests_approve', methods: ['POST'])]
    public function approve(int $id, Request $request): JsonResponse
    {
        $comment = $request->request->get('comment', '');
        
        try {
            $this->approveRequest($id, $comment);
            return new JsonResponse(['success' => true, 'message' => 'Request approved successfully']);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    #[Route('/admin/requests/{id}/reject', name: 'admin_requests_reject', methods: ['POST'])]
    public function reject(int $id, Request $request): JsonResponse
    {
        $reason = $request->request->get('reason', '');
        
        if (empty($reason)) {
            return new JsonResponse(['success' => false, 'message' => 'Rejection reason is required'], 400);
        }

        try {
            $this->rejectRequest($id, $reason);
            return new JsonResponse(['success' => true, 'message' => 'Request rejected successfully']);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    #[Route('/admin/requests/export', name: 'admin_requests_export')]
    public function export(Request $request): Response
    {
        $format = $request->query->get('format', 'csv');
        $filters = [
            'status' => $request->query->get('status', 'all'),
            'type' => $request->query->get('type', 'all'),
            'date_from' => $request->query->get('date_from'),
            'date_to' => $request->query->get('date_to')
        ];

        $data = $this->exportRequests($filters, $format);
        
        $response = new Response($data);
        $response->headers->set('Content-Type', $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="requests_export.' . $format . '"');
        
        return $response;
    }

    private function getRequestsList(int $page, int $limit, string $search, string $status, string $type): array
    {
        // Données simulées - à remplacer par des requêtes réelles
        $allRequests = [
            [
                'id' => 1,
                'reference' => 'REQ-2024-001',
                'type' => 'Birth Certificate',
                'citizen_name' => 'John Doe',
                'citizen_email' => 'john.doe@email.com',
                'status' => 'pending',
                'priority' => 'normal',
                'created_at' => '2024-01-15 14:30:00',
                'updated_at' => '2024-01-16 09:15:00',
                'assigned_to' => 'Civil Registry Office',
                'estimated_completion' => '2024-01-25'
            ],
            [
                'id' => 2,
                'reference' => 'REQ-2024-002',
                'type' => 'Passport Application',
                'citizen_name' => 'Jane Smith',
                'citizen_email' => 'jane.smith@email.com',
                'status' => 'in_progress',
                'priority' => 'high',
                'created_at' => '2024-01-14 09:15:00',
                'updated_at' => '2024-01-17 16:30:00',
                'assigned_to' => 'DGSN',
                'estimated_completion' => '2024-02-14'
            ],
            [
                'id' => 3,
                'reference' => 'REQ-2024-003',
                'type' => 'Marriage Certificate',
                'citizen_name' => 'Paul Martin',
                'citizen_email' => 'paul.martin@email.com',
                'status' => 'completed',
                'priority' => 'normal',
                'created_at' => '2024-01-13 16:45:00',
                'updated_at' => '2024-01-18 11:20:00',
                'assigned_to' => 'Civil Registry Office',
                'estimated_completion' => '2024-01-23'
            ],
            [
                'id' => 4,
                'reference' => 'REQ-2024-004',
                'type' => 'Driving License',
                'citizen_name' => 'Marie Dubois',
                'citizen_email' => 'marie.dubois@email.com',
                'status' => 'under_review',
                'priority' => 'normal',
                'created_at' => '2024-01-12 13:20:00',
                'updated_at' => '2024-01-19 08:45:00',
                'assigned_to' => 'Ministry of Transport',
                'estimated_completion' => '2024-02-12'
            ],
            [
                'id' => 5,
                'reference' => 'REQ-2024-005',
                'type' => 'Business Registration',
                'citizen_name' => 'Pierre Kamdem',
                'citizen_email' => 'pierre.kamdem@email.com',
                'status' => 'rejected',
                'priority' => 'low',
                'created_at' => '2024-01-11 10:30:00',
                'updated_at' => '2024-01-20 14:15:00',
                'assigned_to' => 'CFCE',
                'estimated_completion' => null
            ]
        ];

        // Filtrage simulé
        $filteredRequests = array_filter($allRequests, function($req) use ($search, $status, $type) {
            $matchesSearch = empty($search) || 
                stripos($req['reference'], $search) !== false ||
                stripos($req['citizen_name'], $search) !== false ||
                stripos($req['type'], $search) !== false;
            
            $matchesStatus = $status === 'all' || $req['status'] === $status;
            $matchesType = $type === 'all' || $req['type'] === $type;
            
            return $matchesSearch && $matchesStatus && $matchesType;
        });

        // Pagination simulée
        $offset = ($page - 1) * $limit;
        return array_slice($filteredRequests, $offset, $limit);
    }

    private function getTotalRequestsCount(string $search, string $status, string $type): int
    {
        // Simulation du comptage total avec filtres
        return 45;
    }

    private function getRequestById(int $id): ?array
    {
        // Données simulées pour une demande spécifique
        if ($id === 1) {
            return [
                'id' => 1,
                'reference' => 'REQ-2024-001',
                'type' => 'Birth Certificate',
                'citizen_name' => 'John Doe',
                'citizen_email' => 'john.doe@email.com',
                'citizen_phone' => '+237123456789',
                'status' => 'pending',
                'priority' => 'normal',
                'description' => 'Request for certified copy of birth certificate for passport application',
                'created_at' => '2024-01-15 14:30:00',
                'updated_at' => '2024-01-16 09:15:00',
                'assigned_to' => 'Civil Registry Office',
                'assigned_user' => 'Alice Johnson',
                'estimated_completion' => '2024-01-25',
                'processing_fee' => 500,
                'currency' => 'XAF'
            ];
        }
        
        return null;
    }

    private function getRequestTimeline(int $id): array
    {
        return [
            [
                'date' => '2024-01-15 14:30:00',
                'action' => 'Request submitted',
                'user' => 'John Doe',
                'description' => 'Initial request submitted by citizen'
            ],
            [
                'date' => '2024-01-16 09:15:00',
                'action' => 'Request assigned',
                'user' => 'System',
                'description' => 'Request assigned to Civil Registry Office'
            ],
            [
                'date' => '2024-01-17 11:45:00',
                'action' => 'Documents reviewed',
                'user' => 'Alice Johnson',
                'description' => 'All required documents verified and approved'
            ]
        ];
    }

    private function getRequestDocuments(int $id): array
    {
        return [
            [
                'name' => 'Identity Card Copy',
                'type' => 'PDF',
                'size' => '2.4 MB',
                'uploaded_at' => '2024-01-15 14:35:00',
                'status' => 'approved'
            ],
            [
                'name' => 'Birth Declaration',
                'type' => 'PDF',
                'size' => '1.8 MB',
                'uploaded_at' => '2024-01-15 14:37:00',
                'status' => 'approved'
            ]
        ];
    }

    private function getRequestComments(int $id): array
    {
        return [
            [
                'user' => 'Alice Johnson',
                'comment' => 'All documents are in order. Processing can proceed.',
                'created_at' => '2024-01-17 11:50:00'
            ]
        ];
    }

    private function getStatusesList(): array
    {
        return [
            'all' => 'All Statuses',
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'completed' => 'Completed'
        ];
    }

    private function getRequestTypesList(): array
    {
        return [
            'all' => 'All Types',
            'birth_certificate' => 'Birth Certificate',
            'marriage_certificate' => 'Marriage Certificate',
            'death_certificate' => 'Death Certificate',
            'passport_application' => 'Passport Application',
            'national_id' => 'National ID',
            'driving_license' => 'Driving License',
            'business_registration' => 'Business Registration'
        ];
    }

    private function getPublicEntitiesList(): array
    {
        return [
            1 => 'Civil Registry Office',
            2 => 'DGSN',
            3 => 'Ministry of Transport',
            4 => 'CFCE',
            5 => 'Prefecture'
        ];
    }

    private function getCitizensList(): array
    {
        return [
            1 => 'John Doe',
            2 => 'Jane Smith',
            3 => 'Paul Martin',
            4 => 'Marie Dubois',
            5 => 'Pierre Kamdem'
        ];
    }

    private function createRequest(array $data): int
    {
        // Logique de création d'une nouvelle demande
        return rand(100, 999);
    }

    private function updateRequest(int $id, array $data): void
    {
        // Logique de mise à jour d'une demande
    }

    private function deleteRequest(int $id): void
    {
        // Logique de suppression d'une demande
    }

    private function approveRequest(int $id, string $comment): void
    {
        // Logique d'approbation d'une demande
    }

    private function rejectRequest(int $id, string $reason): void
    {
        // Logique de rejet d'une demande
    }

    private function exportRequests(array $filters, string $format): string
    {
        // Logique d'exportation des demandes
        return 'exported_data';
    }
}