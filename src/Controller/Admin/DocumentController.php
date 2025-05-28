<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DocumentController extends AbstractController
{
    #[Route('/admin/documents', name: 'admin_documents')]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);
        $search = $request->query->get('search', '');
        $type = $request->query->get('type', 'all');
        $status = $request->query->get('status', 'all');

        $documents = $this->getDocumentsList($page, $limit, $search, $type, $status);
        $totalDocuments = $this->getTotalDocumentsCount($search, $type, $status);

        return $this->render('admin/documents/index.html.twig', [
            'documents' => $documents,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalDocuments / $limit),
                'total_items' => $totalDocuments,
                'limit' => $limit
            ],
            'filters' => [
                'search' => $search,
                'type' => $type,
                'status' => $status
            ],
            'document_types' => $this->getDocumentTypesList(),
            'statuses' => $this->getStatusesList(),
            'statistics' => $this->getDocumentStatistics()
        ]);
    }

    #[Route('/admin/documents/{id}', name: 'admin_documents_show')]
    public function show(int $id): Response
    {
        $document = $this->getDocumentById($id);
        
        if (!$document) {
            throw $this->createNotFoundException('Document not found');
        }

        return $this->render('admin/documents/show.html.twig', [
            'document' => $document,
            'versions' => $this->getDocumentVersions($id),
            'related_requests' => $this->getRelatedRequests($id),
            'access_log' => $this->getDocumentAccessLog($id)
        ]);
    }

    #[Route('/admin/documents/new', name: 'admin_documents_new')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $files = $request->files->all();
            $newDocumentId = $this->createDocument($data, $files);
            
            $this->addFlash('success', 'Document created successfully');
            return $this->redirectToRoute('admin_documents_show', ['id' => $newDocumentId]);
        }

        return $this->render('admin/documents/new.html.twig', [
            'document_types' => $this->getDocumentTypesList(),
            'categories' => $this->getDocumentCategoriesList(),
            'templates' => $this->getDocumentTemplatesList()
        ]);
    }

    #[Route('/admin/documents/{id}/edit', name: 'admin_documents_edit')]
    public function edit(int $id, Request $request): Response
    {
        $document = $this->getDocumentById($id);
        
        if (!$document) {
            throw $this->createNotFoundException('Document not found');
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $files = $request->files->all();
            $this->updateDocument($id, $data, $files);
            
            $this->addFlash('success', 'Document updated successfully');
            return $this->redirectToRoute('admin_documents_show', ['id' => $id]);
        }

        return $this->render('admin/documents/edit.html.twig', [
            'document' => $document,
            'document_types' => $this->getDocumentTypesList(),
            'categories' => $this->getDocumentCategoriesList(),
            'templates' => $this->getDocumentTemplatesList()
        ]);
    }

    #[Route('/admin/documents/{id}/delete', name: 'admin_documents_delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        $document = $this->getDocumentById($id);
        
        if (!$document) {
            throw $this->createNotFoundException('Document not found');
        }

        $this->deleteDocument($id);
        $this->addFlash('success', 'Document deleted successfully');
        
        return $this->redirectToRoute('admin_documents');
    }

    private function getDocumentsList(int $page, int $limit, string $search, string $type, string $status): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Birth Certificate Template',
                'type' => 'template',
                'category' => 'Civil Status',
                'status' => 'active',
                'file_name' => 'birth_certificate_template.pdf',
                'file_size' => 2457600,
                'mime_type' => 'application/pdf',
                'version' => '1.2',
                'created_by' => 'Admin User',
                'created_at' => '2024-01-10 14:30:00',
                'updated_at' => '2024-01-15 09:20:00',
                'downloads_count' => 1247,
                'is_public' => true
            ],
            [
                'id' => 2,
                'name' => 'Passport Application Form',
                'type' => 'form',
                'category' => 'Identity Documents',
                'status' => 'active',
                'file_name' => 'passport_form_v2.pdf',
                'file_size' => 1856000,
                'mime_type' => 'application/pdf',
                'version' => '2.0',
                'created_by' => 'DGSN Officer',
                'created_at' => '2024-01-08 11:15:00',
                'updated_at' => '2024-01-18 16:45:00',
                'downloads_count' => 892,
                'is_public' => true
            ],
            [
                'id' => 3,
                'name' => 'User Manual - Registry System',
                'type' => 'manual',
                'category' => 'Documentation',
                'status' => 'draft',
                'file_name' => 'registry_user_manual.docx',
                'file_size' => 5242880,
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'version' => '0.9',
                'created_by' => 'Technical Writer',
                'created_at' => '2024-01-12 10:00:00',
                'updated_at' => '2024-01-19 14:20:00',
                'downloads_count' => 45,
                'is_public' => false
            ]
        ];
    }

    private function getTotalDocumentsCount(string $search, string $type, string $status): int
    {
        return 28;
    }

    private function getDocumentById(int $id): ?array
    {
        if ($id === 1) {
            return [
                'id' => 1,
                'name' => 'Birth Certificate Template',
                'description' => 'Official template for birth certificate generation',
                'type' => 'template',
                'category' => 'Civil Status',
                'status' => 'active',
                'file_name' => 'birth_certificate_template.pdf',
                'file_path' => '/uploads/documents/birth_certificate_template.pdf',
                'file_size' => 2457600,
                'mime_type' => 'application/pdf',
                'version' => '1.2',
                'checksum' => 'a1b2c3d4e5f6g7h8i9j0',
                'created_by' => 'Admin User',
                'created_at' => '2024-01-10 14:30:00',
                'updated_at' => '2024-01-15 09:20:00',
                'downloads_count' => 1247,
                'is_public' => true,
                'expiry_date' => null,
                'tags' => ['official', 'template', 'birth', 'certificate']
            ];
        }
        
        return null;
    }

    private function getDocumentVersions(int $id): array
    {
        return [
            [
                'version' => '1.2',
                'file_name' => 'birth_certificate_template_v1.2.pdf',
                'changes' => 'Updated logo and formatting',
                'created_by' => 'Admin User',
                'created_at' => '2024-01-15 09:20:00',
                'is_current' => true
            ],
            [
                'version' => '1.1',
                'file_name' => 'birth_certificate_template_v1.1.pdf',
                'changes' => 'Fixed typos in French translation',
                'created_by' => 'Registry Officer',
                'created_at' => '2024-01-12 16:45:00',
                'is_current' => false
            ],
            [
                'version' => '1.0',
                'file_name' => 'birth_certificate_template_v1.0.pdf',
                'changes' => 'Initial version',
                'created_by' => 'System Admin',
                'created_at' => '2024-01-10 14:30:00',
                'is_current' => false
            ]
        ];
    }

    private function getRelatedRequests(int $id): array
    {
        return [
            [
                'request_id' => 15,
                'reference' => 'REQ-2024-015',
                'citizen_name' => 'Marie Claire',
                'status' => 'completed',
                'used_at' => '2024-01-18 10:30:00'
            ],
            [
                'request_id' => 23,
                'reference' => 'REQ-2024-023',
                'citizen_name' => 'Jean Baptiste',
                'status' => 'in_progress',
                'used_at' => '2024-01-19 14:15:00'
            ]
        ];
    }

    private function getDocumentAccessLog(int $id): array
    {
        return [
            [
                'user' => 'Registry Officer 1',
                'action' => 'Downloaded',
                'ip_address' => '192.168.1.105',
                'timestamp' => '2024-01-20 09:15:00'
            ],
            [
                'user' => 'Registry Officer 2',
                'action' => 'Viewed',
                'ip_address' => '192.168.1.108',
                'timestamp' => '2024-01-20 10:30:00'
            ]
        ];
    }

    private function getDocumentStatistics(): array
    {
        return [
            'total_documents' => 28,
            'active_documents' => 24,
            'draft_documents' => 4,
            'templates_count' => 12,
            'forms_count' => 8,
            'manuals_count' => 6,
            'other_count' => 2,
            'total_downloads' => 5847,
            'total_size' => '245 MB'
        ];
    }

    private function getDocumentTypesList(): array
    {
        return [
            'all' => 'All Types',
            'template' => 'Templates',
            'form' => 'Forms',
            'manual' => 'Manuals',
            'policy' => 'Policies',
            'guide' => 'Guides',
            'other' => 'Other'
        ];
    }

    private function getDocumentCategoriesList(): array
    {
        return [
            'civil_status' => 'Civil Status',
            'identity_documents' => 'Identity Documents',
            'business' => 'Business',
            'transport' => 'Transport',
            'documentation' => 'Documentation',
            'legal' => 'Legal',
            'administrative' => 'Administrative'
        ];
    }

    private function getDocumentTemplatesList(): array
    {
        return [
            1 => 'Birth Certificate Template',
            2 => 'Marriage Certificate Template',
            3 => 'Death Certificate Template',
            4 => 'Passport Template',
            5 => 'ID Card Template'
        ];
    }

    private function getStatusesList(): array
    {
        return [
            'all' => 'All Statuses',
            'active' => 'Active',
            'draft' => 'Draft',
            'archived' => 'Archived',
            'expired' => 'Expired'
        ];
    }

    private function createDocument(array $data, array $files): int
    {
        return rand(100, 999);
    }

    private function updateDocument(int $id, array $data, array $files): void
    {
        // Logique de mise à jour
    }

    private function deleteDocument(int $id): void
    {
        // Logique de suppression
    }
}