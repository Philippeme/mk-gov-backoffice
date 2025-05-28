<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ProcedureController extends AbstractController
{
    #[Route('/admin/procedures', name: 'admin_procedures')]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);
        $search = $request->query->get('search', '');
        $category = $request->query->get('category', 'all');
        $status = $request->query->get('status', 'all');

        $procedures = $this->getProceduresList($page, $limit, $search, $category, $status);
        $totalProcedures = $this->getTotalProceduresCount($search, $category, $status);

        return $this->render('admin/procedures/index.html.twig', [
            'procedures' => $procedures,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalProcedures / $limit),
                'total_items' => $totalProcedures,
                'limit' => $limit
            ],
            'filters' => [
                'search' => $search,
                'category' => $category,
                'status' => $status
            ],
            'categories' => $this->getProcedureCategoriesList(),
            'statuses' => $this->getStatusesList()
        ]);
    }

    #[Route('/admin/procedures/{id}', name: 'admin_procedures_show')]
    public function show(int $id): Response
    {
        $procedure = $this->getProcedureById($id);
        
        if (!$procedure) {
            throw $this->createNotFoundException('Procedure not found');
        }

        return $this->render('admin/procedures/show.html.twig', [
            'procedure' => $procedure,
            'workflow_steps' => $this->getProcedureWorkflowSteps($id),
            'required_documents' => $this->getProcedureRequiredDocuments($id),
            'statistics' => $this->getProcedureStatistics($id)
        ]);
    }

    #[Route('/admin/procedures/new', name: 'admin_procedures_new')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $newProcedureId = $this->createProcedure($data);
            
            $this->addFlash('success', 'Procedure created successfully');
            return $this->redirectToRoute('admin_procedures_show', ['id' => $newProcedureId]);
        }

        return $this->render('admin/procedures/new.html.twig', [
            'categories' => $this->getProcedureCategoriesList(),
            'public_entities' => $this->getPublicEntitiesList(),
            'document_types' => $this->getDocumentTypesList()
        ]);
    }

    #[Route('/admin/procedures/{id}/edit', name: 'admin_procedures_edit')]
    public function edit(int $id, Request $request): Response
    {
        $procedure = $this->getProcedureById($id);
        
        if (!$procedure) {
            throw $this->createNotFoundException('Procedure not found');
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->updateProcedure($id, $data);
            
            $this->addFlash('success', 'Procedure updated successfully');
            return $this->redirectToRoute('admin_procedures_show', ['id' => $id]);
        }

        return $this->render('admin/procedures/edit.html.twig', [
            'procedure' => $procedure,
            'categories' => $this->getProcedureCategoriesList(),
            'public_entities' => $this->getPublicEntitiesList(),
            'document_types' => $this->getDocumentTypesList(),
            'workflow_steps' => $this->getProcedureWorkflowSteps($id),
            'required_documents' => $this->getProcedureRequiredDocuments($id)
        ]);
    }

    #[Route('/admin/procedures/{id}/delete', name: 'admin_procedures_delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        $procedure = $this->getProcedureById($id);
        
        if (!$procedure) {
            throw $this->createNotFoundException('Procedure not found');
        }

        $this->deleteProcedure($id);
        $this->addFlash('success', 'Procedure deleted successfully');
        
        return $this->redirectToRoute('admin_procedures');
    }

    private function getProceduresList(int $page, int $limit, string $search, string $category, string $status): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Birth Certificate Request',
                'category' => 'Family Services',
                'status' => 'active',
                'processing_time' => '3-5 days',
                'cost' => 500,
                'currency' => 'XAF',
                'responsible_entity' => 'Civil Registry Office',
                'total_requests' => 1250,
                'completion_rate' => 95.2,
                'created_at' => '2024-01-10 10:00:00',
                'updated_at' => '2024-01-18 14:30:00'
            ],
            [
                'id' => 2,
                'name' => 'Passport Application',
                'category' => 'Identity Documents',
                'status' => 'active',
                'processing_time' => '14-21 days',
                'cost' => 110000,
                'currency' => 'XAF',
                'responsible_entity' => 'DGSN',
                'total_requests' => 890,
                'completion_rate' => 87.5,
                'created_at' => '2024-01-08 09:15:00',
                'updated_at' => '2024-01-19 16:45:00'
            ]
        ];
    }

    private function getTotalProceduresCount(string $search, string $category, string $status): int
    {
        return 25;
    }

    private function getProcedureById(int $id): ?array
    {
        if ($id === 1) {
            return [
                'id' => 1,
                'name' => 'Birth Certificate Request',
                'description' => 'Process for requesting certified copies of birth certificates',
                'category' => 'Family Services',
                'status' => 'active',
                'processing_time' => '3-5 days',
                'cost' => 500,
                'currency' => 'XAF',
                'responsible_entity' => 'Civil Registry Office',
                'legal_basis' => 'Law No. 81-02 of June 29, 1981',
                'priority_level' => 'medium',
                'auto_approval' => false,
                'created_at' => '2024-01-10 10:00:00',
                'updated_at' => '2024-01-18 14:30:00'
            ];
        }
        
        return null;
    }

    private function getProcedureWorkflowSteps(int $id): array
    {
        return [
            [
                'order' => 1,
                'name' => 'Request Submission',
                'description' => 'Citizen submits request with required information',
                'responsible_role' => 'Citizen',
                'estimated_duration' => '15 minutes',
                'is_automated' => false
            ],
            [
                'order' => 2,
                'name' => 'Document Verification',
                'description' => 'Registry officer verifies submitted documents',
                'responsible_role' => 'Registry Officer',
                'estimated_duration' => '1 hour',
                'is_automated' => false
            ],
            [
                'order' => 3,
                'name' => 'Record Search',
                'description' => 'Search for birth record in registry database',
                'responsible_role' => 'Registry Officer',
                'estimated_duration' => '30 minutes',
                'is_automated' => true
            ],
            [
                'order' => 4,
                'name' => 'Certificate Generation',
                'description' => 'Generate certified copy with official seal',
                'responsible_role' => 'System',
                'estimated_duration' => '5 minutes',
                'is_automated' => true
            ]
        ];
    }

    private function getProcedureRequiredDocuments(int $id): array
    {
        return [
            [
                'name' => 'Request Form',
                'type' => 'Form',
                'is_mandatory' => true,
                'description' => 'Completed birth certificate request form'
            ],
            [
                'name' => 'Identity Document',
                'type' => 'Identity',
                'is_mandatory' => true,
                'description' => 'Valid identity card or passport of requester'
            ],
            [
                'name' => 'Relationship Proof',
                'type' => 'Legal',
                'is_mandatory' => false,
                'description' => 'Proof of relationship if requesting for another person'
            ]
        ];
    }

    private function getProcedureStatistics(int $id): array
    {
        return [
            'total_requests' => 1250,
            'completed_requests' => 1190,
            'pending_requests' => 45,
            'rejected_requests' => 15,
            'completion_rate' => 95.2,
            'average_processing_time' => 3.8,
            'citizen_satisfaction' => 4.2
        ];
    }

    private function getProcedureCategoriesList(): array
    {
        return [
            'all' => 'All Categories',
            'family' => 'Family Services',
            'identity' => 'Identity Documents',
            'transport' => 'Transport',
            'business' => 'Business Services',
            'education' => 'Education',
            'health' => 'Health Services'
        ];
    }

    private function getStatusesList(): array
    {
        return [
            'all' => 'All Statuses',
            'active' => 'Active',
            'inactive' => 'Inactive',
            'draft' => 'Draft',
            'under_review' => 'Under Review'
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

    private function getDocumentTypesList(): array
    {
        return [
            'form' => 'Form',
            'identity' => 'Identity Document',
            'legal' => 'Legal Document',
            'financial' => 'Financial Document',
            'medical' => 'Medical Document'
        ];
    }

    private function createProcedure(array $data): int
    {
        return rand(100, 999);
    }

    private function updateProcedure(int $id, array $data): void
    {
        // Logique de mise à jour
    }

    private function deleteProcedure(int $id): void
    {
        // Logique de suppression
    }
}