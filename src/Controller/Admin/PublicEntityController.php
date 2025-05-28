<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PublicEntityController extends AbstractController
{
    #[Route('/admin/public-entities', name: 'admin_public_entities')]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);
        $search = $request->query->get('search', '');
        $type = $request->query->get('type', 'all');
        $region = $request->query->get('region', 'all');
        $status = $request->query->get('status', 'all');

        $entities = $this->getPublicEntitiesList($page, $limit, $search, $type, $region, $status);
        $totalEntities = $this->getTotalEntitiesCount($search, $type, $region, $status);

        return $this->render('admin/public_entities/index.html.twig', [
            'entities' => $entities,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalEntities / $limit),
                'total_items' => $totalEntities,
                'limit' => $limit
            ],
            'filters' => [
                'search' => $search,
                'type' => $type,
                'region' => $region,
                'status' => $status
            ],
            'entity_types' => $this->getEntityTypesList(),
            'regions' => $this->getRegionsList(),
            'statuses' => $this->getStatusesList()
        ]);
    }

    #[Route('/admin/public-entities/{id}', name: 'admin_public_entities_show')]
    public function show(int $id): Response
    {
        $entity = $this->getPublicEntityById($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('Public entity not found');
        }

        return $this->render('admin/public_entities/show.html.twig', [
            'entity' => $entity,
            'services' => $this->getEntityServices($id),
            'staff' => $this->getEntityStaff($id),
            'statistics' => $this->getEntityStatistics($id),
            'contact_info' => $this->getEntityContactInfo($id)
        ]);
    }

    #[Route('/admin/public-entities/new', name: 'admin_public_entities_new')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $newEntityId = $this->createPublicEntity($data);
            
            $this->addFlash('success', 'Public entity created successfully');
            return $this->redirectToRoute('admin_public_entities_show', ['id' => $newEntityId]);
        }

        return $this->render('admin/public_entities/new.html.twig', [
            'entity_types' => $this->getEntityTypesList(),
            'regions' => $this->getRegionsList(),
            'parent_entities' => $this->getParentEntitiesList()
        ]);
    }

    #[Route('/admin/public-entities/{id}/edit', name: 'admin_public_entities_edit')]
    public function edit(int $id, Request $request): Response
    {
        $entity = $this->getPublicEntityById($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('Public entity not found');
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $this->updatePublicEntity($id, $data);
            
            $this->addFlash('success', 'Public entity updated successfully');
            return $this->redirectToRoute('admin_public_entities_show', ['id' => $id]);
        }

        return $this->render('admin/public_entities/edit.html.twig', [
            'entity' => $entity,
            'entity_types' => $this->getEntityTypesList(),
            'regions' => $this->getRegionsList(),
            'parent_entities' => $this->getParentEntitiesList()
        ]);
    }

    #[Route('/admin/public-entities/{id}/delete', name: 'admin_public_entities_delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        $entity = $this->getPublicEntityById($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('Public entity not found');
        }

        $this->deletePublicEntity($id);
        $this->addFlash('success', 'Public entity deleted successfully');
        
        return $this->redirectToRoute('admin_public_entities');
    }

    private function getPublicEntitiesList(int $page, int $limit, string $search, string $type, string $region, string $status): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Civil Registry Office - Yaoundé Centre',
                'code' => 'CRO-YDE-CTR',
                'type' => 'civil_registry',
                'region' => 'Centre',
                'department' => 'Mfoundi',
                'commune' => 'Yaoundé 1',
                'status' => 'active',
                'address' => 'Avenue Kennedy, Yaoundé',
                'phone' => '+237 222 23 45 67',
                'email' => 'centre@civilregistry.cm',
                'director' => 'Marie Ngono',
                'staff_count' => 12,
                'services_count' => 8,
                'created_at' => '2024-01-01 00:00:00',
                'updated_at' => '2024-01-15 10:30:00'
            ],
            [
                'id' => 2,
                'name' => 'DGSN - Passport Services Douala',
                'code' => 'DGSN-DLA-PSS',
                'type' => 'security_service',
                'region' => 'Littoral',
                'department' => 'Wouri',
                'commune' => 'Douala 1',
                'status' => 'active',
                'address' => 'Rue de la Liberté, Douala',
                'phone' => '+237 233 42 15 89',
                'email' => 'douala@dgsn.cm',
                'director' => 'Colonel Jean Mbarga',
                'staff_count' => 25,
                'services_count' => 3,
                'created_at' => '2024-01-02 00:00:00',
                'updated_at' => '2024-01-18 14:20:00'
            ],
            [
                'id' => 3,
                'name' => 'Prefecture du Wouri',
                'code' => 'PREF-WOURI',
                'type' => 'prefecture',
                'region' => 'Littoral',
                'department' => 'Wouri',
                'commune' => 'Douala',
                'status' => 'active',
                'address' => 'Place du Gouvernement, Douala',
                'phone' => '+237 233 42 18 76',
                'email' => 'wouri@prefecture.cm',
                'director' => 'Préfet Paul Atangana',
                'staff_count' => 45,
                'services_count' => 15,
                'created_at' => '2024-01-03 00:00:00',
                'updated_at' => '2024-01-19 09:15:00'
            ]
        ];
    }

    private function getTotalEntitiesCount(string $search, string $type, string $region, string $status): int
    {
        return 67;
    }

    private function getPublicEntityById(int $id): ?array
    {
        if ($id === 1) {
            return [
                'id' => 1,
                'name' => 'Civil Registry Office - Yaoundé Centre',
                'code' => 'CRO-YDE-CTR',
                'type' => 'civil_registry',
                'description' => 'Main civil registry office for Yaoundé centre district',
                'region' => 'Centre',
                'department' => 'Mfoundi',
                'commune' => 'Yaoundé 1',
                'arrondissement' => 'Yaoundé 1er',
                'status' => 'active',
                'address' => 'Avenue Kennedy, Yaoundé',
                'postal_code' => 'BP 1234',
                'phone' => '+237 222 23 45 67',
                'fax' => '+237 222 23 45 68',
                'email' => 'centre@civilregistry.cm',
                'website' => 'https://civilregistry.cm/centres/yaounde',
                'director' => 'Marie Ngono',
                'deputy_director' => 'Paul Essomba',
                'staff_count' => 12,
                'services_count' => 8,
                'operating_hours' => '08:00 - 16:00',
                'operating_days' => 'Monday - Friday',
                'license_number' => 'CRO-2024-001',
                'license_expiry' => '2025-12-31',
                'parent_entity_id' => null,
                'coordinates' => [
                    'latitude' => 3.8480,
                    'longitude' => 11.5021
                ],
                'created_at' => '2024-01-01 00:00:00',
                'updated_at' => '2024-01-15 10:30:00'
            ];
        }
        
        return null;
    }

    private function getEntityServices(int $id): array
    {
        return [
            [
                'name' => 'Birth Certificate Copy',
                'code' => 'BC_COPY',
                'description' => 'Certified copy of birth certificate',
                'processing_time' => '1-3 days',
                'cost' => 500,
                'currency' => 'XAF',
                'status' => 'active',
                'monthly_volume' => 245
            ],
            [
                'name' => 'Marriage Certificate Copy',
                'code' => 'MC_COPY',
                'description' => 'Certified copy of marriage certificate',
                'processing_time' => '1-2 days',
                'cost' => 500,
                'currency' => 'XAF',
                'status' => 'active',
                'monthly_volume' => 89
            ],
            [
                'name' => 'Death Certificate Copy',
                'code' => 'DC_COPY',
                'description' => 'Certified copy of death certificate',
                'processing_time' => '1-2 days',
                'cost' => 500,
                'currency' => 'XAF',
                'status' => 'active',
                'monthly_volume' => 34
            ]
        ];
    }

    private function getEntityStaff(int $id): array
    {
        return [
            [
                'name' => 'Marie Ngono',
                'position' => 'Director',
                'email' => 'marie.ngono@civilregistry.cm',
                'phone' => '+237 222 23 45 67',
                'department' => 'Administration',
                'start_date' => '2020-03-15'
            ],
            [
                'name' => 'Paul Essomba',
                'position' => 'Deputy Director',
                'email' => 'paul.essomba@civilregistry.cm',
                'phone' => '+237 222 23 45 69',
                'department' => 'Operations',
                'start_date' => '2021-01-10'
            ],
            [
                'name' => 'Alice Mballa',
                'position' => 'Registry Officer',
                'email' => 'alice.mballa@civilregistry.cm',
                'phone' => '+237 222 23 45 70',
                'department' => 'Birth Certificates',
                'start_date' => '2022-06-01'
            ]
        ];
    }

    private function getEntityStatistics(int $id): array
    {
        return [
            'total_requests_handled' => 1847,
            'monthly_requests' => 368,
            'completion_rate' => 96.2,
            'average_processing_time' => 2.3,
            'citizen_satisfaction' => 4.1,
            'staff_utilization' => 78.5,
            'revenue_generated' => 924500,
            'currency' => 'XAF'
        ];
    }

    private function getEntityContactInfo(int $id): array
    {
        return [
            'emergency_contact' => '+237 222 23 45 99',
            'public_relations' => 'pr@civilregistry.cm',
            'complaints_email' => 'complaints@civilregistry.cm',
            'social_media' => [
                'facebook' => 'https://facebook.com/civilregistrycm',
                'twitter' => 'https://twitter.com/civilregistrycm'
            ]
        ];
    }

    private function getEntityTypesList(): array
    {
        return [
            'all' => 'All Types',
            'civil_registry' => 'Civil Registry Office',
            'security_service' => 'Security Service',
            'prefecture' => 'Prefecture',
            'ministry' => 'Ministry',
            'department' => 'Department',
            'agency' => 'Government Agency',
            'commission' => 'Commission',
            'tribunal' => 'Tribunal'
        ];
    }

    private function getRegionsList(): array
    {
        return [
            'all' => 'All Regions',
            'centre' => 'Centre',
            'littoral' => 'Littoral',
            'nord' => 'Nord',
            'ouest' => 'Ouest',
            'sud' => 'Sud',
            'est' => 'Est',
            'nord-ouest' => 'Nord-Ouest',
            'sud-ouest' => 'Sud-Ouest',
            'adamaoua' => 'Adamaoua',
            'extreme-nord' => 'Extrême-Nord'
        ];
    }

    private function getStatusesList(): array
    {
        return [
            'all' => 'All Statuses',
            'active' => 'Active',
            'inactive' => 'Inactive',
            'suspended' => 'Suspended',
            'under_review' => 'Under Review'
        ];
    }

    private function getParentEntitiesList(): array
    {
        return [
            null => 'No Parent Entity',
            1 => 'Ministry of Justice',
            2 => 'Ministry of Interior',
            3 => 'Ministry of Transport',
            4 => 'Prime Minister Office'
        ];
    }

    private function createPublicEntity(array $data): int
    {
        return rand(100, 999);
    }

    private function updatePublicEntity(int $id, array $data): void
    {
        // Logique de mise à jour
    }

    private function deletePublicEntity(int $id): void
    {
        // Logique de suppression
    }
}