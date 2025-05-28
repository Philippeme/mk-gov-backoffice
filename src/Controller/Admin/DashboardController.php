<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin')]
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function index(Request $request): Response
    {
        // Récupération des filtres depuis la requête
        $regionFilter = $request->query->get('region', 'all');
        $serviceFamilyFilter = $request->query->get('service_family', 'all');
        $statusFilter = $request->query->get('status', 'all');
        $yearFilter = $request->query->get('year', date('Y'));

        // Données simulées pour le dashboard - à remplacer par des requêtes réelles
        $dashboardData = $this->getDashboardData($regionFilter, $serviceFamilyFilter, $statusFilter, $yearFilter);

        return $this->render('admin/dashboard/index.html.twig', [
            'data' => $dashboardData,
            'filters' => [
                'region' => $regionFilter,
                'service_family' => $serviceFamilyFilter,
                'status' => $statusFilter,
                'year' => $yearFilter
            ],
            'regions' => $this->getRegionsList(),
            'service_families' => $this->getServiceFamiliesList(),
            'statuses' => $this->getStatusesList(),
            'years' => $this->getYearsList()
        ]);
    }

    #[Route('/admin/dashboard/api/statistics', name: 'admin_dashboard_api_statistics')]
    public function getStatistics(Request $request): JsonResponse
    {
        $filters = [
            'region' => $request->query->get('region', 'all'),
            'service_family' => $request->query->get('service_family', 'all'),
            'status' => $request->query->get('status', 'all'),
            'year' => $request->query->get('year', date('Y'))
        ];

        $statistics = $this->calculateStatistics($filters);

        return new JsonResponse($statistics);
    }

    #[Route('/admin/dashboard/api/charts/birth-rate', name: 'admin_dashboard_api_birth_rate')]
    public function getBirthRateData(Request $request): JsonResponse
    {
        $year = $request->query->get('year', date('Y'));
        $region = $request->query->get('region', 'all');

        // Données simulées pour le graphique des taux de naissance
        $birthRateData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'datasets' => [
                [
                    'label' => 'Birth Declarations',
                    'data' => [23.26, 25.1, 22.8, 28.3, 24.7, 26.5, 25.9, 27.2, 23.8, 25.4, 24.1, 26.8],
                    'backgroundColor' => 'rgba(255, 159, 64, 0.2)',
                    'borderColor' => 'rgb(255, 159, 64)',
                    'borderWidth' => 2
                ]
            ]
        ];

        return new JsonResponse($birthRateData);
    }

    #[Route('/admin/dashboard/api/charts/missing-births', name: 'admin_dashboard_api_missing_births')]
    public function getMissingBirthsData(Request $request): JsonResponse
    {
        $year = $request->query->get('year', date('Y'));
        $region = $request->query->get('region', 'all');

        // Données simulées pour le graphique des naissances manquées
        $missingBirthsData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'datasets' => [
                [
                    'label' => 'Missing Births %',
                    'data' => [16.98, 15.2, 18.4, 14.7, 17.3, 16.1, 15.8, 17.9, 16.5, 15.3, 17.8, 16.2],
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'borderWidth' => 2
                ]
            ]
        ];

        return new JsonResponse($missingBirthsData);
    }

    #[Route('/admin/dashboard/api/map-data', name: 'admin_dashboard_api_map_data')]
    public function getMapData(Request $request): JsonResponse
    {
        $filters = [
            'region' => $request->query->get('region', 'all'),
            'service_family' => $request->query->get('service_family', 'all'),
            'status' => $request->query->get('status', 'all'),
            'year' => $request->query->get('year', date('Y'))
        ];

        // Données simulées pour la carte interactive
        $mapData = [
            'regions' => [
                [
                    'name' => 'Centre',
                    'coordinates' => [11.5021, 3.8480],
                    'statistics' => [
                        'total_requests' => 1250,
                        'completed' => 980,
                        'pending' => 270,
                        'population' => 4200000
                    ]
                ],
                [
                    'name' => 'Littoral',
                    'coordinates' => [9.7043, 4.0511],
                    'statistics' => [
                        'total_requests' => 2100,
                        'completed' => 1650,
                        'pending' => 450,
                        'population' => 3200000
                    ]
                ],
                [
                    'name' => 'Nord',
                    'coordinates' => [13.3846, 7.3275],
                    'statistics' => [
                        'total_requests' => 890,
                        'completed' => 680,
                        'pending' => 210,
                        'population' => 2800000
                    ]
                ],
                [
                    'name' => 'Ouest',
                    'coordinates' => [10.2661, 5.4698],
                    'statistics' => [
                        'total_requests' => 1450,
                        'completed' => 1120,
                        'pending' => 330,
                        'population' => 1900000
                    ]
                ],
                [
                    'name' => 'Sud',
                    'coordinates' => [11.5564, 2.9283],
                    'statistics' => [
                        'total_requests' => 750,
                        'completed' => 580,
                        'pending' => 170,
                        'population' => 1200000
                    ]
                ]
            ]
        ];

        return new JsonResponse($mapData);
    }

    private function getDashboardData($regionFilter, $serviceFamilyFilter, $statusFilter, $yearFilter): array
    {
        // Données simulées - à remplacer par des requêtes réelles à la base de données
        return [
            'statistics' => [
                'birth_declarations_rate' => 23.26,
                'missing_births_percentage' => 16.98,
                'supplementary_judgments_count' => 12,
                'marriage_opposition_rate' => 2.27
            ],
            'counters' => [
                'regions' => 9,
                'circles' => 48,
                'communes' => 669,
                'civil_centers' => 714,
                'declaration_centers' => 13,
                'tribunals' => 80
            ],
            'recent_requests' => [
                [
                    'id' => 1,
                    'reference' => 'REQ-2024-001',
                    'type' => 'Birth Certificate',
                    'citizen_name' => 'John Doe',
                    'status' => 'pending',
                    'created_at' => '2024-01-15 14:30:00'
                ],
                [
                    'id' => 2,
                    'reference' => 'REQ-2024-002',
                    'type' => 'Passport Application',
                    'citizen_name' => 'Jane Smith',
                    'status' => 'in_progress',
                    'created_at' => '2024-01-14 09:15:00'
                ],
                [
                    'id' => 3,
                    'reference' => 'REQ-2024-003',
                    'type' => 'Marriage Certificate',
                    'citizen_name' => 'Paul Martin',
                    'status' => 'completed',
                    'created_at' => '2024-01-13 16:45:00'
                ]
            ],
            'pending_by_administration' => [
                ['entity' => 'DGSN', 'count' => 45],
                ['entity' => 'Ministry of Justice', 'count' => 23],
                ['entity' => 'Civil Registry', 'count' => 67],
                ['entity' => 'Prefecture', 'count' => 12]
            ],
            'pending_by_citizens' => [
                ['reason' => 'Missing Documents', 'count' => 89],
                ['reason' => 'Payment Pending', 'count' => 34],
                ['reason' => 'Information Required', 'count' => 56],
                ['reason' => 'Appointment Needed', 'count' => 23]
            ]
        ];
    }

    private function calculateStatistics($filters): array
    {
        // Logique de calcul des statistiques basée sur les filtres
        return [
            'total_requests' => 5280,
            'completed_requests' => 4010,
            'pending_requests' => 890,
            'rejected_requests' => 380,
            'completion_rate' => 75.9,
            'average_processing_time' => 3.2
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

    private function getServiceFamiliesList(): array
    {
        return [
            'all' => 'All Service Families',
            'police_justice' => 'Police & Justice',
            'family' => 'Family',
            'transport' => 'Transport',
            'education' => 'Education',
            'business' => 'Business',
            'public_service' => 'Public Service',
            'land_construction' => 'Land & Construction',
            'consular' => 'Consular Services',
            'health' => 'Health',
            'civic_life' => 'Civic Life'
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

    private function getYearsList(): array
    {
        $currentYear = (int) date('Y');
        $years = ['all' => 'All Years'];
        
        for ($year = $currentYear; $year >= $currentYear - 5; $year--) {
            $years[$year] = (string) $year;
        }
        
        return $years;
    }
}